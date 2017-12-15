<?php

echo "hello\n";

try {
    include_once 'includes.php';

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
}
catch (Exception $e){
    echo $e->getFile()."\n";
    echo $e->getMessage()."\n";
    echo $e->getTraceAsString()."\n";
}
catch (ErrorException $e){
    echo $e->getFile()."\n";
    echo $e->getMessage()."\n";
    echo $e->getTraceAsString()."\n";
}

