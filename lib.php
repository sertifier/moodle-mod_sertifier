<?php

use mod_sertifier\apiRest\apiRest;

function sertifier_add_instance($post){
  global $DB,$CFG;
  
  $apiRest = new apiRest($CFG->sertifier_api_key);

  if($post->createDelivery){
    $response = $apiRest->create_delivery($post->deliveryName);
    if($response->hasError){
      print_error($response->message);
    }else{
      $url = new moodle_url('/course/modedit.php', [
        'add' => 'sertifier',
        'course' => $post->course,
        'section' => $post->section,
        'deliveryId' => $response->data
      ]);

      redirect($url);
    }
  }else{
  
    if( isset($post->users) ) {
        $already_recipients_email = array_column($apiRest->get_recipients($post->delivery)->data->recipients, "email");
        $recipients = [];
        foreach ($post->users as $user_id => $issue_certificate) {
            if($issue_certificate) {
                $user = $DB->get_record('user', array('id'=>$user_id), '*', MUST_EXIST);
                if(!in_array($user->email,$already_recipients_email)){
                  $recipients[] = [
                    "name" => $user->firstname . " " . $user->lastname,
                    "email" => $user->email,
                    "issueDate" => date("Y-m-d"),
                    "quickPublish" => true
                  ];
                }
            }
        }
        $response = $apiRest->add_recipients($post->delivery, $recipients);
    }
  
  
    $db_record = new stdClass();
    $db_record->completionactivities = isset($post->completionactivities) ? $post->completionactivities : null;
    $db_record->name = $post->name;
    $db_record->course = $post->course;
    $db_record->finalquiz = $post->finalquiz;
    $db_record->passinggrade = $post->passinggrade;
    $db_record->timecreated = time();
    $db_record->deliveryid = $post->delivery;
  
    return $DB->insert_record('sertifier', $db_record);
    
  }
};

function sertifier_update_instance($post){
  global $DB,$CFG;
  
  $apiRest = new apiRest($CFG->sertifier_api_key);

  if( isset($post->users) ) {
      $already_recipients = $apiRest->get_recipients($post->delivery)->data->recipients;
      $recipients = [];
      $delete_certificate_nos = [];
      foreach ($post->users as $user_id => $issue_certificate) {
          $user = $DB->get_record('user', array('id'=>$user_id), '*', MUST_EXIST);
          $key = array_search($user->email,array_column($already_recipients,"email"));
          if($key !== false){
              if(!$issue_certificate){
                  $delete_certificate_nos[] = $already_recipients[$key]->certificateNo;
              }
          }else if($issue_certificate) {
              $recipients[] = [
                "name" => $user->firstname . " " . $user->lastname,
                "email" => $user->email,
                "issueDate" => date("Y-m-d"),
                "quickPublish" => true
              ];
          }
      }
      $response = $apiRest->add_recipients($post->delivery, $recipients);
      $response = $apiRest->delete_recipients($delete_certificate_nos);
  }

  $db_record = new stdClass();
  $db_record->id = $post->instance;
  $db_record->completionactivities = isset($post->completionactivities) ? $post->completionactivities : null;
  $db_record->name = $post->name;
  $db_record->course = $post->course;
  $db_record->finalquiz = $post->finalquiz;
  $db_record->passinggrade = $post->passinggrade;
  $db_record->timecreated = time();

  return $DB->update_record('sertifier', $db_record);

};

function sertifier_delete_instance($id){
  global $DB;

  // Ensure the certificate exists
  if (!$certificate = $DB->get_record('sertifier', array('id' => $id))) {
      return false;
  }

  return $DB->delete_records('sertifier', array('id' => $id));
};

function delivery_check($delivery){
  if(
    $delivery->type == 2 &&
    $delivery->detailId!="00000000-0000-0000-0000-000000000000" &&
    ($delivery->designId!="00000000-0000-0000-0000-000000000000" || $delivery->badgeId!="00000000-0000-0000-0000-000000000000") &&
    $delivery->emailTemplateId!="00000000-0000-0000-0000-000000000000" &&
    !empty($delivery->emailFromName) &&
    !empty($delivery->mailSubject) 
  ){
    return true;
  }
    return false;

}

function credential_exist($deliveryId,$email)
{
  global $DB,$CFG;
  
  $apiRest = new apiRest($CFG->sertifier_api_key);

  $recipients = $apiRest->get_recipients($deliveryId)->data->recipients;

  $recipient_emails = array_column($recipients,"email");

  if(in_array($email,$recipient_emails)){
    return true;
  }else {
    return false;
  }
  
}

function sertifier_quiz_submission_handler($event)
{
	global $DB, $CFG;

  $apiRest = new apiRest($CFG->sertifier_api_key);

	$attempt = $event->get_record_snapshot('quiz_attempts', $event->objectid);
	$quiz    = $event->get_record_snapshot('quiz', $attempt->quiz);
  $user 	 = $DB->get_record('user', array('id' => $event->relateduserid));
  $sertifier_records = $DB->get_records('sertifier', ['course' => $event->courseid]);

  if($sertifier_records) {
		foreach ($sertifier_records as $record) {
			if( $record && ($record->finalquiz) ) {
        if($quiz->id == $record->finalquiz) {

          $check_credential = credential_exist($record->deliveryid, $user->email);

          if(!$check_credential) {
            $users_grade = min( ( quiz_get_best_grade($quiz, $user->id) / $quiz->grade ) * 100, 100);

            if($users_grade >= $record->passinggrade) {
              $apiRest->add_recipients($record->deliveryid,[
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

function sertifier_course_completed_handler($event){
    global $DB, $CFG;

    $apiRest = new apiRest($CFG->sertifier_api_key);

    $user = $DB->get_record('user', array('id' => $event->relateduserid));

    $sertifier_records = $DB->get_records('sertifier', ['course' => $event->courseid]);
    if($sertifier_records) {
      foreach ($sertifier_records as $record) {
        if($record && $record->completionactivities){
          $check_credential = credential_exist($record->deliveryid, $user->email);
          if(!$check_credential){
            $response = $apiRest->add_recipients($record->deliveryid, [
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