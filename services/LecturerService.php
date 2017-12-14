<?php
include 'BaseService.php';
include '../model/Lecturer.php';

class LecturerService extends BaseService
{
    public function __construct()
    {
        parent::__construct("lecturer", Lecturer::class);
    }
}