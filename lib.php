<?php
// This file is part of the Sertifier Certificate module for Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Certificate module core interaction API
 *
 * @package    mod_sertifier
 * @copyright  Sertifier <hr@sertifier.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_sertifier\apiRest\apiRest;

defined('MOODLE_INTERNAL') || die();

function sertifier_add_instance($post) {
    global $DB, $CFG;

    $apirest = new apiRest();

    if ($post->createDelivery) {
        $response = $apirest->create_delivery($post->deliveryName);
        if ($response->hasError) {
            print_error($response->message);
        } else {
            $url = new moodle_url('/course/modedit.php', [
                'add' => 'sertifier',
                'course' => $post->course,
                'section' => $post->section,
                'deliveryId' => $response->data
            ]);

            redirect($url);
        }
    } else {

        if (!$post->delivery) {
            print_error("Click the create button to create a new delivery.");
        }

        if ( isset($post->users) ) {
            $alreadyrecipientsemail = array_column($apirest->get_recipients($post->delivery)->data->recipients, "email");
            $recipients = [];
            foreach ($post->users as $userid => $issuecertificate) {
                if ($issuecertificate) {
                    $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
                    if (!in_array($user->email, $alreadyrecipientsemail)) {
                        $recipients[] = [
                            "name" => $user->firstname . " " . $user->lastname,
                            "email" => $user->email,
                            "issueDate" => date("Y-m-d"),
                            "quickPublish" => true
                        ];
                    }
                }
            }
            $response = $apirest->add_recipients($post->delivery, $recipients);
        }

        $dbrecord = new stdClass();
        $dbrecord->completionactivities = isset($post->completionactivities) ? $post->completionactivities : null;
        $dbrecord->name = $post->name;
        $dbrecord->course = $post->course;
        $dbrecord->finalquiz = $post->finalquiz;
        $dbrecord->passinggrade = $post->passinggrade;
        $dbrecord->timecreated = time();
        $dbrecord->deliveryid = $post->delivery;

        return $DB->insert_record('sertifier', $dbrecord);
    }
};

function sertifier_update_instance($post) {
    global $DB, $CFG;

    $apirest = new apiRest();

    if ( isset($post->users) ) {
        $alreadyrecipients = $apirest->get_recipients($post->delivery)->data->recipients;
        $recipients = [];
        $deletecertificatenos = [];
        foreach ($post->users as $userid => $issuecertificate) {
            $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
            $key = array_search($user->email, array_column($alreadyrecipients, "email"));
            if ($key !== false) {
                if (!$issuecertificate) {
                    $deletecertificatenos[] = $alreadyrecipients[$key]->certificateNo;
                }
            } else if ($issuecertificate) {
                $recipients[] = [
                    "name" => $user->firstname . " " . $user->lastname,
                    "email" => $user->email,
                    "issueDate" => date("Y-m-d"),
                    "quickPublish" => true
                ];
            }
        }
        $response = $apirest->add_recipients($post->delivery, $recipients);
        $response = $apirest->delete_recipients($deletecertificatenos);
    }

    $dbrecord = new stdClass();
    $dbrecord->id = $post->instance;
    $dbrecord->completionactivities = isset($post->completionactivities) ? $post->completionactivities : null;
    $dbrecord->name = $post->name;
    $dbrecord->course = $post->course;
    $dbrecord->finalquiz = $post->finalquiz;
    $dbrecord->passinggrade = $post->passinggrade;
    $dbrecord->timecreated = time();

    return $DB->update_record('sertifier', $dbrecord);
};

function sertifier_delete_instance($id) {
    global $DB;

    if (!$certificate = $DB->get_record('sertifier', array('id' => $id))) {
        return false;
    }

    return $DB->delete_records('sertifier', array('id' => $id));
};

function sertifier_delivery_check($delivery) {
    if (
        $delivery->type == 2 &&
        $delivery->detailId != "00000000-0000-0000-0000-000000000000" &&
        ($delivery->designId != "00000000-0000-0000-0000-000000000000" || $delivery->badgeId != "00000000-0000-0000-0000-000000000000") &&
        $delivery->emailTemplateId != "00000000-0000-0000-0000-000000000000" &&
        !empty($delivery->emailFromName) &&
        !empty($delivery->mailSubject)
    ) {
        return true;
    }

    return false;
}

function sertifier_credential_exist($deliveryid, $email) {
    global $DB, $CFG;

    $apirest = new apiRest();

    $recipients = $apirest->get_recipients($deliveryid)->data->recipients;

    $recipientemails = array_column($recipients, "email");

    if (in_array($email, $recipientemails)) {
        return true;
    }else {
        return false;
    }
}

function sertifier_quiz_submission_handler($event) {
    global $DB, $CFG;

    $apirest = new apiRest();

    $attempt = $event->get_record_snapshot('quiz_attempts', $event->objectid);
    $quiz = $event->get_record_snapshot('quiz', $attempt->quiz);
    $user = $DB->get_record('user', array('id' => $event->relateduserid));
    $sertifierrecords = $DB->get_records('sertifier', ['course' => $event->courseid]);

    if ($sertifierrecords) {
        foreach ($sertifierrecords as $record) {
            if ( $record && ($record->finalquiz) ) {
                if ($quiz->id == $record->finalquiz) {

                    $checkcredential = sertifier_credential_exist($record->deliveryid, $user->email);

                    if (!$checkcredential) {
                        $usersgrade = min( ( quiz_get_best_grade($quiz, $user->id) / $quiz->grade ) * 100, 100);

                        if ($usersgrade >= $record->passinggrade) {
                            $apirest->add_recipients($record->deliveryid, [
                                [
                                    "name" => $user->firstname . " " . $user->lastname,
                                    "email" => $user->email,
                                    "issueDate" => date("Y-m-d"),
                                    "quickPublish" => true
                                ]
                            ]);
                        }
                    }
                }
            }
        }
    }
}

function sertifier_course_completed_handler($event) {
    global $DB, $CFG;

    $apirest = new apiRest();

    $user = $DB->get_record('user', array('id' => $event->relateduserid));

    $sertifierrecords = $DB->get_records('sertifier', ['course' => $event->courseid]);
    if ($sertifierrecords) {
        foreach ($sertifierrecords as $record) {
            if ($record && $record->completionactivities) {
                $checkcredential = sertifier_credential_exist($record->deliveryid, $user->email);
                if (!$checkcredential) {
                    $response = $apirest->add_recipients($record->deliveryid, [
                        [
                            "name" => $user->firstname . " " . $user->lastname,
                            "email" => $user->email,
                            "issueDate" => date("Y-m-d"),
                            "quickPublish" => true
                        ]
                    ]);
                }
            }
        }
    }
}