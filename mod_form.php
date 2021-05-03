<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}
 
require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/sertifier/lib.php');

use mod_sertifier\apiRest\apiRest;

class mod_sertifier_mod_form extends moodleform_mod {
 
    function definition() {
        global $CFG, $DB, $OUTPUT;
        $updatingcert = false;

        if(!isset($CFG->sertifier_api_key)) {
            print_error('Please set your API Key first in the plugin settings.');
        }

        $apiRest = new apiRest($CFG->sertifier_api_key);

        $deliveries = $apiRest->get_all_deliveries();
        $deliveryFilter = array();
        foreach ($deliveries->data->deliveries as $delivery) {
            if(delivery_check($delivery)){
                $deliveryFilter[$delivery->id] = $delivery->title;
            }
        }

        // Update form init
        if (optional_param('update', '', PARAM_INT)) {
            $updatingcert = true;
            $cm_id = optional_param('update', '', PARAM_INT);
            $cm = get_coursemodule_from_id('sertifier', $cm_id, 0, false, MUST_EXIST);
            $id = $cm->course;
            $course = $DB->get_record('course', array('id'=> $id), '*', MUST_EXIST);
            $sertifier_certificate = $DB->get_record('sertifier', array('id'=> $cm->instance), '*', MUST_EXIST);
            $recipients = $apiRest->get_recipients($sertifier_certificate->deliveryid)->data->recipients;
        } else if(optional_param('course', '', PARAM_INT)) { // New form init
            $id =  optional_param('course', '', PARAM_INT);
            $course = $DB->get_record('course', array('id'=> $id), '*', MUST_EXIST);
        }

        // Load user data
        $context = context_course::instance($course->id);
        $users = get_enrolled_users($context, "mod/sertifier:view", null, 'u.*');

        // Load final quiz choices
        $quiz_choices = array(0 => 'None');
        if($quizes = $DB->get_records_select('quiz', 'course = :course_id', array('course_id' => $id) )) {
            foreach( $quizes as $quiz ) { 
                $quiz_choices[$quiz->id] = $quiz->name;
            }
        }

        $mform =& $this->_form;
        $mform->addElement('hidden', 'course', $id);
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('static', 'overview', get_string('overview', 'sertifier'), get_string('activitydescription', 'sertifier'));
        if(!$updatingcert){
            // Create New Delivery
            if(isset($_GET['deliveryId'])){
                $mform->addElement('static', 'edit', '', get_string('createdDelivery', 'sertifier', $_GET['deliveryId']));
            }else {
                $new_delivery = array();
                $new_delivery[] =& $mform->createElement('text', 'deliveryName', "Delivery Name", ['style'=>'width: 296px']);
                $new_delivery[] =& $mform->createElement('submit', 'createDelivery', get_string('create'), ['style'=>'width: 100px']);
                $mform->addGroup($new_delivery, 'new_delivery', get_string('createDelivery', 'sertifier'), array(' '), false);
            }

            // Select Old Delivery
            if(count($deliveryFilter)>0){
                $mform->addElement('select', 'delivery', get_string('selectedDelivery', 'sertifier'), $deliveryFilter,array('style'=>'width: 400px'));
            }else {
                $mform->addElement('static', 'delivery', get_string('selectedDelivery', 'sertifier'), get_string('notFoundDelivery','sertifier'));
            }
            $mform->addRule('delivery', null, 'required', null, 'client');
            if(isset($_GET['deliveryId']) && array_key_exists($_GET['deliveryId'],$deliveryFilter)){
                $mform->setDefault('delivery',$_GET['deliveryId']);
            }
        }else {
            $mform->addElement('static', 'selectdelivery', get_string('selectedDelivery', 'sertifier'), get_string('cantchangedelivery','sertifier', $deliveryFilter[$sertifier_certificate->deliveryid]));
            $mform->addElement('hidden','delivery',$sertifier_certificate->deliveryid);
        }
        

        $mform->addElement('text', 'name', get_string('activityname', 'sertifier'), array('style'=>'width: 400px'));
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', $course->fullname);

        // set recipients
        $mform->addElement('header', 'chooseusers', get_string('selectRecHeader', 'sertifier'));
        $this->add_checkbox_controller(1, 'Select All/None');

        foreach( $users as $user ) {
            if($updatingcert){
                $key = array_search($user->email,array_column($recipients,"email"));
                if($key !== false){
                    $mform->addElement('advcheckbox', 'users['.$user->id.']', $user->firstname . ' ' . $user->lastname . ' - ' . $user->email . " - <a href='https://verified.cv/en/verify/".$recipients[$key]->certificateNo."' target='_blank'>View Credential</a>");
                    $mform->setDefault('users['.$user->id.']', 1);
                }else {
                    $mform->addElement('advcheckbox', 'users['.$user->id.']', $user->firstname . ' ' . $user->lastname . ' - ' . $user->email, null, array('group' => 1));
                }            
            }else {
                $mform->addElement('advcheckbox', 'users['.$user->id.']', $user->firstname . ' ' . $user->lastname . ' - ' . $user->email, null, array('group' => 1));
            }
        }

        // Quiz check
        $mform->addElement('header', 'gradeissue', get_string('gradeissueheader', 'sertifier'));
        $mform->addElement('select', 'finalquiz', get_string('chooseexam', 'sertifier'), $quiz_choices);
        $mform->addElement('text', 'passinggrade', get_string('passinggrade', 'sertifier'));
        $mform->setType('passinggrade', PARAM_INT);
        $mform->setDefault('passinggrade', 70);

        // Send finish course
        $mform->addElement('header', 'completionissue', get_string('completionissueheader', 'sertifier'));
        $mform->addElement('checkbox', 'completionactivities', get_string('completionissuecheckbox', 'sertifier'));
        if($updatingcert && isset( $sertifier_certificate->completionactivities )) {
            $mform->setDefault('completionactivities', 1);
        }
 
        $this->standard_coursemodule_elements();
 
        $this->add_action_buttons();
    }
}