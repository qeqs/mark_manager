<?php
include "../sql/DataBase.php";

const MANY_TO_MANY = "@manyToMany";
const ONE_TO_MANY = "@oneToMany";
const ONE_TO_ONE = "@oneToOne";


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
        $object = $this->executeQuery($sql);

        $this->processRelationship($object, "GET");
        return $object;
    }

    public function getAll()
    {
        $sql = "SELECT * FROM {$this->table}";
        $objects = $this->executeQueryForClassArray($sql, $this->Class);

        foreach ($objects as $object) {
            $this->processRelationship($object, "GET");
        }
        return $objects;
    }

    public function save($object, $isCascade)
    {

        $sql = "INSERT INTO {$this->table} SET ";

        $sql .= $this->getColumnsAsString($object);

        $sql .= ' ON DUPLICATE KEY UPDATE '; // MySQL

        $sql .= $this->getColumnsAsString($object);

        $this->execute($sql);

        if (!isset($object->id))
            $object->id = $this->db->insert_id;


        if ($isCascade) {
            $this->processRelationship($object, "SAVE");
        }

    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id={$id}";
        $this->execute($sql);

        $object = $this->createObject($this->Class);
        $object->id = $id;
        $this->processRelationship($object, "DELETE");
    }

    final protected function executeQuery($sql)
    {
        return $this->executeQueryForClass($sql, $this->Class);
    }

    final protected function execute($sql)
    {
        return $this->db->query($sql);
    }

    final protected function executeQueryForClass($sql, $class)
    {
        $arr = $this->executeQueryForClassArray($sql, $class);
        return $arr[0];
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

    final protected function getColumns($object)
    {
        $reflect = new ReflectionClass($object);
        $columns = array();
        foreach ($reflect->getProperties() as $prop) {
            if ($this->isRelationship($object, $prop->getName())) continue;
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

    //ORM below

    private function processRelationship($object, $processType){
        switch ($processType){
            case "GET":
                $propertyNames = $this->getAnnotatedProperties($object);
                foreach ($propertyNames as $name) {
                    $this->processSelectRelationship($object, $name, $this->getAnnotationName($object, $name));
                }
                break;
            case "SAVE":
                $propertyNames = $this->getAnnotatedProperties($object);
                foreach ($propertyNames as $name) {
                    $this->processSaveRelationship($object, $name, $this->getAnnotationName($object, $name));
                }
                break;
            case "DELETE":
                $propertyNames = $this->getAnnotatedProperties($object);
                foreach ($propertyNames as $name) {
                    $this->processDeleteRelationship($object, $name, $this->getAnnotationName($object, $name));
                }
                break;
        }
    }


    private function processSelectRelationship($object, $propName, $relationType)
    {
        switch ($relationType) {
            case MANY_TO_MANY:
                $this->processSelectManyToMany($object, $propName);
                break;
            case ONE_TO_MANY:
                $this->processSelectOneToMany($object, $propName);
                break;
            case ONE_TO_ONE:
                //todo
                break;
        }
    }

    private function processSaveRelationship($object, $propName, $relationType)
    {
        switch ($relationType) {
            case MANY_TO_MANY:
                $this->processSaveManyToMany($object, $propName);
                break;
            case ONE_TO_MANY:
                $this->processSaveOneToMany($object, $propName);
                break;
            case ONE_TO_ONE:
                //todo
                break;
        }
    }

    private function processDeleteRelationship($object, $propName, $relashionType)
    {
        switch ($relashionType) {
            case MANY_TO_MANY:
                $this->processDeleteManyToMany($object, $propName);
                break;
            case ONE_TO_MANY:
                $this->processDeleteOneToMany($object, $propName);
                break;
            case ONE_TO_ONE:
                break;
        }
    }

    private function processSelectManyToMany($object, $propName)
    {
        $id = $object->id;
        $annotationParams = $this->getParams($object, $propName);
        $relationClass = get_class($annotationParams[0]);
        $relationProperty = $annotationParams[1];
        $crossRefTable = $annotationParams[2];
        $columnName = $annotationParams[3];
        $relationColumnName = $annotationParams[4];

        $relationService = null;
        foreach ($this->getAllChildClasses() as $service) {
            if ($service->getClass() == $relationClass) {
                $relationService = $service;
            }
        }

        $getRelationIdsSql = "SELECT {$relationColumnName} FROM {$crossRefTable} crt WHERE crt.{$columnName}={$id}";
        $getReverseRelationIdsSql = "SELECT * FROM {$crossRefTable} crt WHERE crt.{$relationColumnName}=";

        $relationIds = $this->executeQueryArrayNum($getRelationIdsSql);

        $relationTable = $relationService->getTable();
        $sql = "SELECT * FROM {$relationTable} WHERE id=";
        $relationObjects = array();
        foreach ($relationIds as $relationId) {
            $relationOfRelationsIds = $this->executeQueryArrayAssoc($getReverseRelationIdsSql.$relationId);
            foreach ($relationOfRelationsIds as $relation) {
                $relationObj = $this->executeQueryForClass($sql.$relation[$relationColumnName], $relationClass);
                $this->setRelation($relationObj, $relationProperty, $this->get($relation[$columnName]));
                $relationObjects[] = $relationObj;
            }
        }

        $this->setRelation($object, $propName, $relationObjects);
    }

    private function processSelectOneToMany($object, $propName)
    {
        $id = $object->id;
        $annotationParams = $this->getParams($object, $propName);
        $relationClass = get_class($annotationParams[0]);
        $relationColumnName = $annotationParams[1];

        $relationService = null;
        foreach ($this->getAllChildClasses() as $service) {
            if ($service->getClass() == $relationClass) {
                $relationService = $service;
            }
        }

        $getRelationIdSql = "SELECT {$relationColumnName} FROM {$this->table} crt WHERE id={$id}";

        $relationIds = $this->executeQueryArrayNum($getRelationIdSql);
        $relationId = $relationIds[0][0];

        $relationTable = $relationService->getTable();
        $sql = "SELECT * FROM {$relationTable} WHERE id={$relationId}";
        $relationObject = $this->executeQueryForClass($sql, $relationClass);
        $this->setRelation($object, $propName, $relationObject);
    }

    private function processSaveManyToMany($object, $propName)
    {
        $id = $object->id;
        $annotationParams = $this->getParams($object, $propName);
        $relationClass = get_class($annotationParams[0]);
        $crossRefTable = $annotationParams[2];
        $columnName = $annotationParams[3];
        $relationColumnName = $annotationParams[4];

        $relationService = null;
        foreach ($this->getAllChildClasses() as $service) {
            if ($service->getClass() == $relationClass) {
                $relationService = $service;
            }
        }

        $getRelationIdsSql = "SELECT {$relationColumnName} FROM {$crossRefTable} crt WHERE crt.{$columnName}={$id}";

        $relations = $this->getRelation($object, $propName);
        $relationIds = $this->executeQueryArrayNum($getRelationIdsSql);
        foreach ($relations as $relation) {


            if ($relationService->save($relation, false)) {

                $relationId = $relation->id;
                if (!in_array($relationId, $relationIds)) {
                    if (!isset($relationId)) $relationId = $this->db->insert_id;
                    $sql = "INSERT INTO {$crossRefTable} SET ";
                    $sql .= "({$relationColumnName} = {$relationId}, {$columnName} = {$id})";
                    $this->execute($sql);
                }
            }

        }
    }

    private function processSaveOneToMany($object, $propName)
    {
        $id = $object->id;
        $annotationParams = $this->getParams($object, $propName);
        $relationClass = get_class($annotationParams[0]);
        $relationColumnName = $annotationParams[1];

        $relationService = null;
        foreach ($this->getAllChildClasses() as $service) {
            if ($service->getClass() == $relationClass) {
                $relationService = $service;
            }
        }

        $relation = $this->getRelation($object, $propName);

        if ($relationService->save($relation, false)) {

            $relationId = $relation->id;
            if (!isset($relationId)) $relationId = $this->db->insert_id;
            $sql = "UPDATE {$this->table} SET ";
            $sql .= " {$relationColumnName} = {$relationId} WHERE id={$id}";
            $this->execute($sql);
        }
    }

    private function processDeleteManyToMany($object, $propName)
    {
        $id = $object->id;
        $annotationParams = $this->getParams($object, $propName);
        $crossRefTable = $annotationParams[2];
        $columnName = $annotationParams[3];

        $sql = "DELETE FROM {$crossRefTable} WHERE {$columnName}={$id}";
        $this->execute($sql);
    }

    private function processDeleteOneToMany($object, $propName)
    {

    }


    private function isRelationship($object, $propName)
    {
        return $this->isManyToMany($object, $propName) || $this->isOneToMany($object, $propName) || $this->isOneToOne($object, $propName);
    }

    private function isOneToMany($object, $propName)
    {
        return $this->isAnnotated($object, $propName, ONE_TO_MANY);
    }

    private function isManyToMany($object, $propName)
    {
        return $this->isAnnotated($object, $propName, MANY_TO_MANY);
    }

    private function isOneToOne($object, $propName)
    {
        return $this->isAnnotated($object, $propName, ONE_TO_ONE);
    }

    private function getParams($object, $propName)
    {
        $reflect = new ReflectionClass($object);
        $docComment = $reflect->getProperty($propName)->getDocComment();
        $annotation = $this->getAnnotationString($docComment);
        return $this->getAnnotationParams($annotation);
    }

    private function getAnnotationParams($annotationStr)
    {
        $matches = null;
        $returnValue = preg_match_all('#\\(.*?\\)#', $annotationStr, $matches, PREG_SET_ORDER);
        if ($returnValue)
            return explode(',', substr($matches[0][0], 1, strlen($matches[0][0]) - 1));
        return array();
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

    private function getAnnotationName($object, $propName)
    {
        $reflect = new ReflectionClass($object);
        $annotationStr = $this->getAnnotationString($reflect->getProperty($propName)->getDocComment());
        $matches = null;
        $returnValue = preg_match_all('#@(.*?)\\(#s', $annotationStr, $matches, PREG_SET_ORDER);
        if ($returnValue)
            return substr($matches[0][0], 0, strlen($matches[0][0]) - 1);
        return 0;
    }

    private function setRelation($object, $propName, $value)
    {
        $reflect = new ReflectionClass($object);
        $reflect->getProperty($propName)->setValue($value);
    }

    private function getRelation($object, $propName)
    {
        $reflect = new ReflectionClass($object);
        return $reflect->getProperty($propName)->getValue();
    }

    private function getAllChildClasses()
    {
        $children = array();
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, 'BaseService')) {
                $instance = new ReflectionClass($class);
                $children[] = $instance->newInstance();
            }
        }
        return $children;
    }

    private function getAnnotatedProperties($object)
    {
        $properties = array();
        $reflect = new ReflectionClass($object);
        foreach ($reflect->getProperties() as $prop) {
            if ($this->isRelationship($object, $prop->getName())) {
                $properties[] = $prop->getName();
            }
        }
        return $properties;
    }

    private function createObject($class){
        $reflect = new ReflectionClass($class);
        return $reflect->newInstance();
    }

    private function contains($str, $what)
    {
        return strpos($str, $what) !== false;
    }


}