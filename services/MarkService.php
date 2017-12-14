<?php
include 'BaseService.php';
include '../model/Mark.php';

class MarkService extends BaseService
{
    public function __construct()
    {
        parent::__construct("mark", Mark::class);
    }
}