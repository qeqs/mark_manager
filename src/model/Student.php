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
     * @manyToMany(Subject,students,subject_student,student_id,subject_id)
     */
    var $subjects;


}

?>