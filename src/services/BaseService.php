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
        $objects = $this->executeQueryForClassArray($sql, $this->Class);
        return $objects;
    }

    public function save($object)
    {
        if ($object == null) return false;
        $withId = isset($object->id);

        $sql = "INSERT INTO {$this->table}(";

        $sql .= $this->getColumnsAsString($object, $withId) . ") ";

        $sql .= "VALUES(" . $this->getValuesAsString($object, $withId) . ") ";

        $sql .= 'ON DUPLICATE KEY UPDATE '; // MySQL

        $sql .= $this->getColumnsWithValuesAsString($object, $withId);

        $res = $this->execute($sql);

        if (!$withId)
            $object->id = $this->db->insert_id;

        return $res;

    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id={$id}";
        $this->execute($sql);
    }

    final protected function executeQuery($sql)
    {
        return $this->executeQueryForClass($sql, $this->Class);
    }

    final protected function execute($sql)
    {
        error_log("SQL: " . $sql);
        return $this->db->query($sql);
    }

    final protected function executeQueryForClass($sql, $class)
    {
        $arr = $this->executeQueryForClassArray($sql, $class);
        if (count($arr) > 0) {
            return $arr[0];
        }
        return null;
    }

    final protected function executeQueryForClassArray($sql, $class)
    {
        $Objects = array();
        if ($rst = $this->execute($sql)) {
            while ($Object = $rst->fetch_object($class)) {
                $Objects[] = $Object;
            }
            $rst->free();
        } else {
            throw new DatabaseException($sql, $this->db->error, $this->db->errno);
        }
        return $Objects;
    }

    final protected function executeQueryArrayNum($sql)
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

    final protected function executeQueryArrayAssoc($sql)
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

    final protected function getColumns($object, $excludes = array())
    {
        $reflect = new ReflectionClass($object);
        $columns = array();
        foreach ($reflect->getProperties() as $prop) {
            if(!in_array($prop->getName(), $excludes)) {
                $val = $prop->getValue($object);
                $columns[$prop->getName()] = is_string($val) ? "'" . $val . "'" : $val;
            }
        }
        return $columns;
    }

    final protected function getColumnsWithValuesAsString($object, $withId)
    {
        $sql = "";
        $columns = $this->getColumns($object);
        foreach ($columns as $key => $value) {
            if ($withId | $key != "id") {
                $sql .= ", {$key}={$value} ";
            }
        }
        return substr($sql, 1);
    }

    final protected function getColumnsAsString($object, $withId)
    {
        $sql = "";
        $columns = $this->getColumns($object);
        foreach ($columns as $key => $value) {
            if ($withId | $key != "id") {
                $sql .= ", {$key} ";
            }
        }
        return substr($sql, 1);
    }

    final protected function getValuesAsString($object, $withId)
    {
        $sql = "";
        $columns = $this->getColumns($object);
        foreach ($columns as $key => $value) {
            if ($withId | $key != "id") {
                $sql .= ", {$value} ";
            }
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