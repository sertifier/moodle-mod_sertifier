<?php
require_once("../../config.php");

use mod_sertifier\apiRest\apiRest;
$apiRest = new apiRest($CFG->sertifier_api_key);

$id = required_param('id', PARAM_INT);    // Course Module ID

$cm = get_coursemodule_from_id('sertifier', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=> $cm->course), '*', MUST_EXIST);
$sertifier_record = $DB->get_record('sertifier', array('id'=> $cm->instance), '*', MUST_EXIST);

require_login($course->id, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/sertifier:view', $context);

// Initialize $PAGE, compute blocks
$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/mod/sertifier/view.php', array('id' => $cm->id));
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title(format_string($sertifier_record->name));
$PAGE->set_heading(format_string($course->fullname));

// User has admin privileges, show table of certificates.
if(has_capability('mod/sertifier:manage', $context)) {
	
	
	$credentials = $apiRest->get_recipients($sertifier_record->deliveryid)->data->recipients;

	$button_style = "display: inline-block; line-height:28px; padding: 0 16px; border: 2px solid #09705b; font-size:12px; background:#09705b; color: #fff; border-radius: 5px; margin-right: 15px; transition: all .3s ease 0s!important;";
	$button_onMouseOver = "this.style.background='#FFF'; this.style.color='#09705b'; this.style.borderColor='#bfe3d9'; this.style.textDecoration='none';";
	$button_onMouseOut = "this.style.background='#09705b'; this.style.color='#FFF'; this.style.borderColor='#09705b';";

	$table = new html_table();
	$table->head  = [
		get_string('name', 'sertifier'), 
		get_string('email', 'sertifier'), 
		get_string('credentialNo', 'sertifier'), 
		get_string('issueDate', 'sertifier')
	];

	foreach ($credentials as $credential) {
			$date = date_format( date_create($credential->createDate), "M d, Y" ) ;
			$url = 'https://verified.cv/en/verify/'.$credential->certificateNo;
	  	$table->data[] = array ( 
				$credential->name,
				$credential->email,
	  		"<a href='$url' target='_blank'>$credential->certificateNo</a>",
				$date
			);
	}

	echo $OUTPUT->header();
	
	echo html_writer::tag( 'h3', $sertifier_record->name);

	echo html_writer::tag( 'p', get_string('viewmanagementdesc', 'sertifier') );
	echo html_writer::tag( 'a', get_string('gotoreports', 'sertifier'), ["href"=>"https://app.sertifier.com/en/home/reports?deliveryId=" . $sertifier_record->deliveryid, "target" => "_blank", "style" => $button_style, "onMouseOver" => $button_onMouseOver, "onMouseOut" => $button_onMouseOut] );
	
	echo html_writer::tag( 'br', null );
	echo html_writer::table($table);
	echo $OUTPUT->footer($course);
}else {
	echo $OUTPUT->header();
	
	$credential = false;
	$credentials = $apiRest->get_recipients($sertifier_record->deliveryid)->data->recipients;
	$key = array_search($USER->email,array_column($credentials,"email"));
	if($key !== false){
		$credential = $credentials[$key];
	}

	$button_style = "display: inline-block; line-height:28px; padding: 0 16px; border: 2px solid #09705b; font-size:12px; background:#09705b; color: #fff; border-radius: 5px; margin-right: 15px; transition: all .3s ease 0s!important;";
	$button_onMouseOver = "this.style.background='#FFF'; this.style.color='#09705b'; this.style.borderColor='#bfe3d9'; this.style.textDecoration='none';";
	$button_onMouseOut = "this.style.background='#09705b'; this.style.color='#FFF'; this.style.borderColor='#09705b';";

	echo html_writer::tag( 'h3', $sertifier_record->name);

	if($credential){
		echo html_writer::tag( 'p', get_string('existcertificate', 'sertifier') );
		echo html_writer::tag( 'a', get_string('viewcredential', 'sertifier'), ["href"=>"https://verified.cv/en/verify/" . $credential->certificateNo, "target" => "_blank", "style" => $button_style, "onMouseOver" => $button_onMouseOver, "onMouseOut" => $button_onMouseOut] );
	}else {
		echo html_writer::tag( 'p', get_string('nonexistcertificate', 'sertifier') );
	}
	
	
	echo $OUTPUT->footer($course);
}