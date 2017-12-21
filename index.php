<?php


include_once 'includes.php';

$mark = new Mark();
$mark->type = "quiz";
$subject = new Subject();
$subject->name = "TOI";
$subject->lecturers = array();
$subject->students = array();
$mark->subject = $subject;

$markService = new MarkService();
if ($markService->save($mark, true)) {
    echo "hello\n";
} else {

    echo "not hello\n";
}
$mark = $markService->get($mark->id);
error_log($mark->id);
echo $mark->id;

$sql = "INSERT INTO `mark`(`id`, `type`, `subject_id`) VALUES (1, 'att', 0)";
DataBase::getInstance()->query($sql);
$mark = $markService->get(1);

error_log($mark->id);
echo $mark->id;
