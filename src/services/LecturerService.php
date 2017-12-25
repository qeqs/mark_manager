<?php

class LecturerService extends BaseService
{
    public function __construct()
    {
        parent::__construct("lecturer", Lecturer::class);
    }
}