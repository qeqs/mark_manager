<?php
include 'BaseService.php';
include '../model/Student.php';

class StudentService extends BaseService
{
    public function __construct()
    {
        parent::__construct("student", Student::class);
    }
}