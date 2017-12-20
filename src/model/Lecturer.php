<?php

/**
 * Object represents table 'lecturer'
 */
class Lecturer
{

    var $id;

    var $name;

    var $surname;

    /**
     * @manyToMany(Subject,lecturers,subject_lecturer,lecturer_id,subject_id)
     */
    var $subjects;

}

?>