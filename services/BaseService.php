<?php
include '../sql/DataBase.php';

abstract class BaseService
{

    protected $db;
    protected $table;
    protected $relationships;
    protected $Class;

    protected function __construct($table, $class, $relationships)
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

    public function save($object)
    {

        $sql = "INSERT INTO {$this->table} SET ";

        $sql .= $this->getColumnsAsString($object);

        $sql .= ' ON DUPLICATE KEY UPDATE '; // MySQL

        $this->executeQuery($sql);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id={$id}";
        return $this->executeQuery($sql);
    }

    final protected function executeQuery($sql)
    {
        if ($rst = $this->db->query($sql)) {
            if ($Object = $rst->fetch_object($this->Class)) {
                $rst->free();
            } else {
                throw new DatabaseException($sql, $this->db->error, $this->db->errno);
            }
        } else {
            throw new DatabaseException($sql, $this->db->error, $this->db->errno);
        }
        return $Object;
    }

    final protected function getColumns($object)
    {
        $reflect = new ReflectionClass($object);
        $columns = array();
        foreach ($reflect->getProperties() as $prop) {
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


}