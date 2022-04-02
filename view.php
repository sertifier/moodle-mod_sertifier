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
 * Handles viewing a certificate
 *
 * @package    mod_sertifier
 * @copyright  Sertifier <hr@sertifier.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");

use mod_sertifier\apiRest\apiRest;
$apirest = new apiRest();

$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('sertifier', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$sertifierrecord = $DB->get_record('sertifier', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course->id, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/sertifier:view', $context);

$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/mod/sertifier/view.php', array('id' => $cm->id));
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title(format_string($sertifierrecord->name));
$PAGE->set_heading(format_string($course->fullname));

if (has_capability('mod/sertifier:manage', $context)) {

    $credentials = $apirest->get_recipients($sertifierrecord->deliveryid)->data->recipients;

    $buttonstyle = "display: inline-block; line-height:28px; padding: 0 16px; border: 2px solid #09705b; font-size:12px; background:#09705b; color: #fff; border-radius: 5px; margin-right: 15px; transition: all .3s ease 0s!important;";
    $buttononmouseover = "this.style.background='#FFF'; this.style.color='#09705b'; this.style.borderColor='#bfe3d9'; this.style.textDecoration='none';";
    $buttononmouseout = "this.style.background='#09705b'; this.style.color='#FFF'; this.style.borderColor='#09705b';";

    $table = new html_table();
    $table->head = [
        get_string('name', 'sertifier'), 
        get_string('email', 'sertifier'), 
        get_string('credentialNo', 'sertifier'), 
        get_string('issueDate', 'sertifier')
    ];

    foreach ($credentials as $credential) {
        $date = date_format( date_create($credential->createDate), "M d, Y" );
        $url = 'https://verified.cv/en/verify/'.$credential->certificateNo;
        $table->data[] = array (
            $credential->name,
            $credential->email,
            "<a href='$url' target='_blank'>$credential->certificateNo</a>",
            $date
        );
    }

    echo $OUTPUT->header();

    echo html_writer::tag( 'h3', $sertifierrecord->name);

    echo html_writer::tag( 'p', get_string('viewmanagementdesc', 'sertifier') );
    echo html_writer::tag( 'a', get_string('gotoreports', 'sertifier'), [
        "href" => "https://app.sertifier.com/en/home/reports?deliveryId=" . $sertifierrecord->deliveryid,
        "target" => "_blank",
        "style" => $buttonstyle,
        "onMouseOver" => $buttononmouseover,
        "onMouseOut" => $buttononmouseout
    ]);

    echo html_writer::tag( 'br', null );
    echo html_writer::table($table);
    echo $OUTPUT->footer($course);
} else {
    echo $OUTPUT->header();

    $credential = false;
    $credentials = $apirest->get_recipients($sertifierrecord->deliveryid)->data->recipients;
    $key = array_search($USER->email, array_column($credentials, "email"));
    if ($key !== false) {
        $credential = $credentials[$key];
    }

    $buttonstyle = "display: inline-block; line-height:28px; padding: 0 16px; border: 2px solid #09705b; font-size:12px; background:#09705b; color: #fff; border-radius: 5px; margin-right: 15px; transition: all .3s ease 0s!important;";
    $buttononmouseover = "this.style.background='#FFF'; this.style.color='#09705b'; this.style.borderColor='#bfe3d9'; this.style.textDecoration='none';";
    $buttononmouseout = "this.style.background='#09705b'; this.style.color='#FFF'; this.style.borderColor='#09705b';";

    echo html_writer::tag( 'h3', $sertifierrecord->name);

    if ($credential) {
        echo html_writer::tag( 'p', get_string('existcertificate', 'sertifier') );
        echo html_writer::tag( 'a', get_string('viewcredential', 'sertifier'), [
            "href" => "https://verified.cv/en/verify/" . $credential->certificateNo,
            "target" => "_blank",
            "style" => $buttonstyle,
            "onMouseOver" => $buttononmouseover,
            "onMouseOut" => $buttononmouseout
        ]);
    } else {
        echo html_writer::tag( 'p', get_string('nonexistcertificate', 'sertifier') );
    }

    echo $OUTPUT->footer($course);
}
