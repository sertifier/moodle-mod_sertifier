<?php

defined('MOODLE_INTERNAL') || die();

$observers = array(
    // Listen for finished quizes.
    array(
        'eventname' => '\mod_quiz\event\attempt_submitted',
        'includefile' => '/mod/sertifier/lib.php',
        'callback' => 'sertifier_quiz_submission_handler',
        'internal' => false
    ),
    // Course completed only runs with a cron job. There's no other way to ensure course completion without the Moodle course completion cron job running.
     array(
        'eventname'   => '\core\event\course_completed',
        'includefile' => '/mod/sertifier/lib.php',
        'callback'    => 'sertifier_course_completed_handler',
        'internal' => false
     )
);
