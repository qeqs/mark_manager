<?php


include_once 'includes.php';

$mark = new Mark();
$mark->type = "quiz";

$student = new Student();
$student->surname = "Lygin";
$student->name = "Vadim";
$student->course = 4;
$student->courseType = "bachelor";
$student->group = 2;

$studentService = new StudentService();
$studentService->save($student);

$lecturer = new Lecturer();
$lecturer->name = "Alex";
$lecturer->surname = "Sirota";

$lecturerService = new LecturerService();
$lecturerService->save($lecturer);

$subject = new Subject();
$subject->name = "TOI";
$subject->lecturers = array($lecturer);
$subject->students = array($student);

$mark->subject = $subject;

$markService = new MarkService();
if ($markService->save($mark)) {
    echo "hello\n";
} else {
    echo "not hello\n";
}
$mark = $markService->get($mark->id);
error_log("Mark id: ".$mark->id);

error_log("Subject id: ".$mark->subject->id);

error_log("Student id: ".$mark->subject->students[0]->id);

$markService->delete($mark->id);

