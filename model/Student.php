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
     * @manyToMany(Subject, SELECT sl.subject_id FROM subject_student sl WHERE sl.student_id=?)
     */
    var $subjects;


}

?>