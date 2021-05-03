<?php

require_once('../../config.php');
require_once('lib.php');

$id = required_param('id', PARAM_INT);

$course = $DB->get_record('course', array('id'=> $id), '*', MUST_EXIST);

require_course_login($course);

$strcertificates = get_string('modulenameplural', 'sertifier');
$strname  = get_string("name");

$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/mod/sertifier/index.php', array('id'=>$course->id));
$PAGE->navbar->add($strcertificates);
$PAGE->set_title($strcertificates);
$PAGE->set_heading($course->fullname);

if (!$certificates = get_all_instances_in_course('sertifier', $course)) {
    echo $OUTPUT->header();
    notice(get_string('nocertificates', 'sertifier'), "$CFG->wwwroot/course/view.php?id=$course->id");
    echo $OUTPUT->footer();
    exit();
}

$table = new html_table();

$table->head  = array ($strname, get_string('datecreated', 'sertifier'));

foreach ($certificates as $certificate) {
  $link = html_writer::tag('a', $certificate->name, array('href' => $CFG->wwwroot . '/mod/sertifier/view.php?id=' . $certificate->coursemodule));
  $issued = date("M d, Y",$certificate->timecreated);
  $table->data[] = array ($link, $issued);
}

echo $OUTPUT->header();
echo html_writer::tag( 'h3', get_string('indexheader', 'sertifier', $course->fullname) );
echo html_writer::table($table);
echo $OUTPUT->footer();
