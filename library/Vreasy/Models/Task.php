<?php

namespace Vreasy\Models;

use Vreasy\Query\Builder;
use Vreasy\Models\Status;
use Vreasy\Models\TaskStatusHistory;

class Task extends Base
{
    // Protected attributes should match table columns
    protected $id;
    protected $deadline;
    protected $assigned_name;
    protected $assigned_phone;
    protected $status_id;
    protected $created_at;
    protected $updated_at;
   

    public function __construct()
    {
        // Validation is done run by Valitron library
        $this->validates(
            'required',
            ['deadline', 'assigned_name', 'assigned_phone']
        );
        $this->validates(
            'date',
            ['created_at', 'updated_at']
        );
        $this->validates(
            'integer',
            ['id']
        );
        $this->status();
    }
    
    public function status(){
        return $this->belongsTo('status_id', 'Vreasy\Models\Status');
    }

    public function save()
    {
        // Base class forward all static:: method calls directly to Zend_Db
        if ($this->isValid()) {
            $this->updated_at = gmdate(DATE_FORMAT);
            if ($this->isNew()) {
                $this->created_at = $this->updated_at;
                $this->status_id = Status::loadByName('pending');
                static::insert('tasks', $this->attributesForDb());
                $this->id = static::lastInsertId();
                $this->_updateTaskStatusHistory();
            } else {                
                $statusBeforeChange = Task::findOrInit(['id' => $this->id])->status_id;
                
                static::update(
                    'tasks',
                    $this->attributesForDb(),
                    ['id = ?' => $this->id]
                );
                
                if ($statusBeforeChange != $this->status_id) {
                    $this->_updateTaskStatusHistory();
                }
            }
            return $this->id;
        }
    }

    public static function findOrInit($id)
    {
        $task = new Task();
        if ($tasksFound = static::where(['id' => (int)$id])) {
            $task = array_pop($tasksFound);
        }
        return $task;
    }


    public static function where($params, $opts = [])
    {
        // Default options' values
        $limit = 0;
        $start = 0;
        $orderBy = ['created_at'];
        $orderDirection = ['asc'];
        extract($opts, EXTR_IF_EXISTS);
        $orderBy = array_flatten([$orderBy]);
        $orderDirection = array_flatten([$orderDirection]);

        // Return value
        $collection = [];
        // Build the query
        list($where, $values) = Builder::expandWhere(
            $params,
            ['wildcard' => true, 'prefix' => 't.']);

        // Select header
        $select = "SELECT t.* FROM tasks AS t";

        // Build order by
        foreach ($orderBy as $i => $value) {
            $dir = isset($orderDirection[$i]) ? $orderDirection[$i] : 'ASC';
            $orderBy[$i] = "`$value` $dir";
        }
        $orderBy = implode(', ', $orderBy);

        $limitClause = '';
        if ($limit) {
            $limitClause = "LIMIT $start, $limit";
        }

        $orderByClause = '';
        if ($orderBy) {
            $orderByClause = "ORDER BY $orderBy";
        }
        if ($where) {
            $where = "WHERE $where";
        }

        $sql = "$select $where $orderByClause $limitClause";
        if ($res = static::fetchAll($sql, $values)) {
            foreach ($res as $row) {
                $collection[] = static::instanceWith($row);
           }
        }
        return $collection;
    }
    
   

    public function belongsTo($property, $classOrInstance)
    {
        // Unsetting the propery will force to call the magic methods afterwards
        // so we can hook in and do our stuff
        unset($this->$property);
        $field = '_assoc_'.$property;
        if (is_object($classOrInstance)) {
            $classType = get_class($classOrInstance);
            $this->$field = new BelongsTo($property, $classType, $classOrInstance);
        } else {
            // An empty One association
            $one = new BelongsTo($property, $classOrInstance);
            $this->$field = $one;
        }
        return $this->$field;
    }
    
    public static function hydrate($instance, $params)
    {
        foreach ($params as $k => $v) {
            // A collection where the value is not serialized
            if ($instance->$k instanceof Collection /* && !is_string($v) */ &&
                !$v instanceof $instance->$k->classType && $v
            ) {
                if ($instance->$k instanceof One) {
                    $instance->$k->buildAssociation($v);
                } elseif ($instance->$k instanceof BelongsTo) {
                    $instance->$k->buildAssociation($v);
                } elseif ($instance->$k instanceof Many) {
                    $instance->$k->buildCollection($v);
                }
            } else {
                // Attempts to parse integer values
                $instance->$k = ctype_digit($v) ? (int)$v : $v;
            }
        }
        return $instance;
    }
    
    private function _updateTaskStatusHistory($taskId = null) {
        TaskStatusHistory::instanceWith([
            'task_id' => ($taskId ? $taskId : $this->id),
            'status_id' => Status::findOrInit($this->status_id->id),
            'created_at' => $this->updated_at
        ])->save();
    }
}
