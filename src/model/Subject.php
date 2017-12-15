<?php

/**
 * Object represents table 'subject'
 */
class Subject
{

    var $id;

    var $name;

    /*
     * @manyToMany(Lecturer,subjects,subject_lecturer,subject_id,lecturer_id)
     */
    var $lecturers;

    /*
     * @manyToMany(Student,subjects,subject_student,subject_id,student_id)
     */
    var $students;


}

?>