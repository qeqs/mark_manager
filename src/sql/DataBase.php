<?php

include 'DataBase.inc';
/**
 * DataBase singleton class
 */
class DataBase
{
    /*
     * Database variable
     * @var mysqli
     */
    protected static $db;

    /**
     * Constructor
     */
    final protected function __construct()
    {
        //no public constructor for singleton class
    }

    /**
     * Instantiator static method
     * As of PHP 5.3.0, PHP implements a feature called late static bindings which
     * can be used to reference the called class in a context of static inheritance.
     * @return MySQLi
     */
    public static function getInstance()
    {
        if (!is_object(self::$db)) {
            self::$db = new mysql(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        }
        return self::$db;
    }

    /**
     * Destructor for garbage collector
     */
    protected function __destruct()
    {
        if (self::$db) self::$db->close();
    }

    /**
     * Forbid cloning in sigleton class
     */
    protected function __clone()
    {
        //no possibility for cloning of singleton class
    }
}


?>