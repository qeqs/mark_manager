<?php

/**
 * Object represents table 'mark'
 */
class Mark
{

    var $id;

    var $type;

    /*
     * @oneToMany(Subject, SELECT subject_id FROM mark WHERE id=?)
     */
    var $subject;
}

?>