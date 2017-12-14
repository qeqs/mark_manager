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
     * @manyToMany(Subject, SELECT sl.subject_id FROM subject_lecturer sl WHERE sl.lecturer_id=?)
     */
    var $subjects;

}

?>