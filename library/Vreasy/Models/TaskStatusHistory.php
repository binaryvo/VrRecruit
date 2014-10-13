<?php

namespace Vreasy\Models;

use Vreasy\Query\Builder;

class TaskStatusHistory extends Base
{
    // Protected attributes should match table columns
    protected $id;
    protected $task_id;
    protected $status_id;
    protected $created_at;

    public function __construct()
    {
        // Validation is done run by Valitron library
        $this->validates(
            'required',
            ['task_id']
        );
        $this->validates(
            'date',
            ['created_at']
        );
        $this->validates(
            'integer',
            ['id']
        );
        
        $this->belongsTo('status_id', 'Vreasy\Models\Status');
    }

    public function save()
    {
        // Base class forward all static:: method calls directly to Zend_Db
        if ($this->isValid()) {
            $this->created_at = gmdate(DATE_FORMAT);
            static::insert('task_status', $this->attributesForDb());
            $this->id = static::lastInsertId();
            return $this->id;
        }
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
            ['wildcard' => true, 'prefix' => 'ts.']);

        // Select header
        $select = "SELECT ts.* FROM task_status AS ts";

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
}
