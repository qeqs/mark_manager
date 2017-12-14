<?php

class SubjectManager extends BaseService
{
    public function __construct()
    {
        parent::__construct("subject", Subject::class);
    }
}