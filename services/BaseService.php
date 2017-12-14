<?php

class BaseService
{
    protected $db;
    protected $table;
    protected $Class;

    protected function __construct($table, $class)
    {
        $this->Class = $class;
        $this->table = $table;
        $this->db = DataBase::getInstance();
    }

    public function get($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id={$id}";
        return $this->executeQuery($sql);
    }

    public function getAll()
    {
        $sql = "SELECT * FROM {$this->table}";
        return $this->executeQuery($sql);
    }

    public function save($object, $isCascade)
    {

        $sql = "INSERT INTO {$this->table} SET ";

        $sql .= $this->getColumnsAsString($object);

        $sql .= ' ON DUPLICATE KEY UPDATE '; // MySQL

        $sql .= $this->getColumnsAsString($object);

        return $this->execute($sql);

    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id={$id}";
        return $this->executeQuery($sql);
    }

    final protected function executeQuery($sql)
    {
        return $this->_executeQuery($sql, $this->Class);
    }

    final protected function execute($sql)
    {
        return $this->db->query($sql);
    }

    final protected function _executeQuery($sql, $class)
    {
        if ($rst = $this->execute($sql)) {
            if ($Object = $rst->fetch_object($class)) {
                $rst->free();

            } else {
                throw new DatabaseException($sql, $this->db->error, $this->db->errno);
            }
        } else {
            throw new DatabaseException($sql, $this->db->error, $this->db->errno);
        }
        return $Object;
    }

    final protected function _executeQueryArrayNum($sql)
    {
        $res = array();
        if ($rst = $this->execute($sql)) {
            if ($arr = $rst->fetch_all(MYSQLI_NUM)) {
                foreach ($arr as $ar)
                    foreach ($ar as $obj)
                        $res[] = $obj;
                $rst->free();

            } else {
                throw new DatabaseException($sql, $this->db->error, $this->db->errno);
            }
        } else {
            throw new DatabaseException($sql, $this->db->error, $this->db->errno);
        }
        return $res;
    }

    final protected function _executeQueryArrayAssoc($sql)
    {
        if ($rst = $this->execute($sql)) {
            if ($arr = $rst->fetch_all(MYSQLI_ASSOC)) {
                $rst->free();

            } else {
                throw new DatabaseException($sql, $this->db->error, $this->db->errno);
            }
        } else {
            throw new DatabaseException($sql, $this->db->error, $this->db->errno);
        }
        return $arr;
    }

    final protected function getColumns($object)
    {
        $reflect = new ReflectionClass($object);
        $columns = array();
        foreach ($reflect->getProperties() as $prop) {
            if ($this->isRelationship($object, $prop)) continue;
            $columns[$prop->getName()] = $prop->getValue();
        }
        return $columns;
    }

    final protected function getColumnsAsString($object)
    {
        $sql = "";
        $columns = $this->getColumns($object);
        foreach ($columns as $key => $value) {
            $sql .= ", {$key}={$value} ";
        }
        return substr($sql, 1);
    }

    public function getClass()
    {
        return $this->Class;
    }

    public function getTable()
    {
        return $this->table;
    }
}