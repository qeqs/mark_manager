<?php
include '../sql/DataBase.php';

abstract class BaseService
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

    //ORM below



    protected function isRelationship($object, $propName)
    {
        return $this->isManyToMany($object, $propName) || $this->isOneToMany($object, $propName) || $this->isOneToOne($object, $propName);
    }

    protected function isOneToMany($object, $propName)
    {
        return $this->isAnnotated($object, $propName, "@oneToMany");
    }

    protected function isManyToMany($object, $propName)
    {
        return $this->isAnnotated($object, $propName, "@manyToMany");
    }

    protected function isOneToOne($object, $propName)
    {
        return $this->isAnnotated($object, $propName, "@oneToOne");
    }

    protected function getAnnotationParam($annotationStr){
        $matches = null;
        $returnValue = preg_match_all('#\\(.*?\\)#', $annotationStr, $matches, PREG_SET_ORDER);
        if($returnValue)
            return substr($matches[0][0],1, strlen($matches[0][0])-1);
        return '';
    }

    private function isAnnotated($object, $propName, $annotation)
    {
        $reflect = new ReflectionClass($object);
        $comment = $reflect->getProperty($propName)->getDocComment();
        return $this->contains($comment, $annotation);
    }

    private function getAnnotationString($doc)
    {
        $matches = null;
        $returnValue = preg_match_all('#@(.*?)\\n#s', $doc, $matches, PREG_SET_ORDER);
        if ($returnValue)
            return $matches[0][0];
        return 0;
    }

    private function contains($str, $what)
    {
        return strpos($str, $what) !== false;
    }


}