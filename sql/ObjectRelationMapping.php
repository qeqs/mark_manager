<?php

/**
 * used annotations: repository, manyToMany, manyToOne, oneToOne
 */
class ObjectRelationMapping
{

    private $repFolder;

    public function __construct($repositoriesFolder)
    {
        $this->repFolder = $repositoriesFolder;
    }

    public function queryRelationship($object, $propName){
        $reflect = new ReflectionClass($object);
        $property = $reflect->getProperty($propName);

        if($this->isAnnotated($object, $propName, "@manyToMany")){

        }
        if($this->isAnnotated($object, $propName, "@manyToOne")){

        }
        if($this->isAnnotated($object, $propName, "@oneToOne")){
            $propReflect =  new ReflectionClass($property->getValue());
            $id = $propReflect->getProperty("id")->getValue();
            if(isset($id))
                return $id;

        }


    }

    public function getRelationshipRepository($class){

    }


    public function isAnnotated($object, $propName, $annotation)
    {
        $reflect = new ReflectionClass($object);
        $comment = $reflect->getProperty($propName)->getDocComment();
        return $this->contains($comment, $annotation);
    }

    private function contains($str, $what)
    {
        return strpos($str, $what) !== false;
    }
}