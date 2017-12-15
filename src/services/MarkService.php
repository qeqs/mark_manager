<?php

class MarkService extends BaseService
{
    public function __construct()
    {
        parent::__construct("mark", Mark::class);
    }
}