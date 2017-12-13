<?php

/**
 * Object represents table 'student'
 */
class Student
{

    var $id;

    var $name;

    var $surname;

    var $course;

    var $courseType;

    var $group;

    /*
     * @manyToMany(Subject)
     */
    var $subjects;


}

?>