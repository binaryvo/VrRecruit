<?php

namespace Vreasy\Models;

use Vreasy\Query\Builder;

class Status extends Base
{
    // Protected attributes should match table columns
    protected $id;
    protected $name;
    protected $order;

    public function __construct()
    {
        // Validation is done run by Valitron library
        $this->validates(
            'required',
            ['name']
        );
        $this->validates(
            'integer',
            ['id', 'order']
        );
    }

    public function save()
    {
        // Base class forward all static:: method calls directly to Zend_Db
        if ($this->isValid()) {
            $this->created_at = gmdate(DATE_FORMAT);
            static::insert('status', $this->attributesForDb());
            $this->id = static::lastInsertId();
            return $this->id;
        }
    }

    public static function findOrInit($id)
    {
        $status = new Status();
        if ($statusesFound = static::where(['id' => (int)$id])) {
            $status = array_pop($statusesFound);
        }
        return $status;
    }
    
    public static function loadByName($name)
    {
        $status = new Status();
        if ($statusesFound = static::where(['name' => $name])) {
            $status = array_pop($statusesFound);
        }
        return $status;
    }
    
    public function getNext()
    {
        if ($this->isValid()) {
            $status = new Status();
            
            $values = ['order' => $this->order, 'id' => $this->id];
            
            // Select header
            $select = 'SELECT s.* FROM status AS s';
                        
            $where = 'WHERE s.order > :order OR (s.order = :order AND s.id > :id)';
            $orderByClause = ' ORDER BY s.order ASC, id ASC';
            $limitClause = ' LIMIT 1';
        
            $sql = "$select $where $orderByClause $limitClause";
            if ($res = static::fetchRow($sql, $values)) {
                $status = static::instanceWith($res);
            }
            return $status;
        }
    }

    public static function where($params, $opts = [])
    {
        // Default options' values
        $limit = 0;
        $start = 0;
        $orderBy = ['order'];
        $orderDirection = ['asc'];
        extract($opts, EXTR_IF_EXISTS);
        $orderBy = array_flatten([$orderBy]);
        $orderDirection = array_flatten([$orderDirection]);

        // Return value
        $collection = [];
        // Build the query
        list($where, $values) = Builder::expandWhere(
            $params,
            ['wildcard' => true, 'prefix' => 's.']);

        // Select header
        $select = "SELECT s.* FROM status AS s";

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
    
    public static function hydrate($instance, $params)
    {
        if (!is_array($params) && ctype_digit($params)){
            return static::findOrInit($params);
        } else {
            return parent::hydrate($instance, $params);
        }
    }
}
