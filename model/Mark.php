<?php

/**
 * Object represents table 'mark'
 */
class Mark
{

    var $id;

    var $type;

    /*
     * @oneToMany(Subject)
     */
    var $subject;
}

?>