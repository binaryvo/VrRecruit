<?php

namespace Vreasy\Models;

use Vreasy\Query\Builder;

class Message extends Base
{
    // Protected attributes should match table columns
    protected $id;
    protected $sid;
    protected $task_id;
    protected $text;
    protected $recipient_number;
    protected $direction;
    protected $created_at;

    public function __construct()
    {
        // Validation is done run by Valitron library
        $this->validates(
            'required',
            ['task_id', 'text']
        );
        $this->validates(
            'date',
            ['created_at']
        );
        $this->validates(
            'integer',
            ['id', 'task_id']
        );
    }

    public function save()
    {
        // Base class forward all static:: method calls directly to Zend_Db
        if ($this->isValid()) {
            $this->created_at = gmdate(DATE_FORMAT);
            static::insert('message', $this->attributesForDb());
            $this->id = static::lastInsertId();
            return $this->id;
        }
    }
    
    public static function findLastByRecipient($recipientNumber)
    {
        $message = new Message();
        if ($messagesFound = static::where(['recipient_number' => $recipientNumber])) {
            $message = array_pop($messagesFound);
        }
        return $message;
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
            ['wildcard' => true, 'prefix' => 'm.']);

        // Select header
        $select = "SELECT m.* FROM message AS m";

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
        
        $res = static::fetchAll($sql, $values);
        if ($res) {
            foreach ($res as $row) {
                $collection[] = static::instanceWith($row);
            }
        }
        
        return $collection;
    }
}
