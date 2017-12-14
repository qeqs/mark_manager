<?php

/**
 * Object represents table 'subject'
 */
class Subject
{

    var $id;

    var $name;

    /*
     * @manyToMany(Lecturer, SELECT sl.lecturer_id FROM subject_lecturer sl WHERE sl.subject_id=?)
     */
    var $lecturers;

    /*
     * @manyToMany(Student, SELECT sl.student_id FROM subject_student sl WHERE sl.subject_id=?)
     */
    var $students;


}

?>