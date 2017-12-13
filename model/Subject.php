<?php

/**
 * Object represents table 'subject'
 */
class Subject
{

    var $id;

    var $name;

    /*
     * @manyToMany(Lecturer)
     */
    var $lecturers;

    /*
     * @manyToMany(Student)
     */
    var $students;


}

?>