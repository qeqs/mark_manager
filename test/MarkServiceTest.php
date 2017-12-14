<?php

include "../services/MarkService.php";
include "../model/Subject.php";

$mark = new Mark();
$mark->type = "quiz";
$subject = new Subject();
$subject->name = "TOI";
$subject->lecturers = array();
$subject->students = array();
$mark->subject = $subject;

$markService = new MarkService();

$markService->save($mark, true);

echo $markService->get($mark->id);