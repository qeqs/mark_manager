<?php

/**
 * Object represents table 'lecturer'
 */
class Lecturer
{

    var $id;

    var $name;

    var $surname;

    /*
     * @manyToMany(Subject)
     */
    var $subjects;

}

?>