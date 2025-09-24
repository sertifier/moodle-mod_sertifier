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
 * Instance add/edit form
 *
 * @package    mod_sertifier
 * @copyright  Sertifier <hr@sertifier.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/sertifier/lib.php');

use mod_sertifier\apiRest\apiRest;

/**
 * Sertifier settings form.
 *
 * @package    mod_sertifier
 * @subpackage sertifier
 * @copyright  Sertifier <hr@sertifier.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_sertifier_mod_form extends moodleform_mod {

    /**
     * Called to define this moodle form
     *
     * @return void
     */
    public function definition() {
        global $CFG, $DB, $OUTPUT;
        $updatingcert = false;

        if (get_config('sertifier', 'api_key') == null) {
            throw new moodle_exception('Please set your API Key first in the plugin settings.');
        }

        $apirest = new apiRest();

        $deliveries = $apirest->get_all_deliveries();
        $deliveryfilter = array();
        foreach ($deliveries->data->deliveries as $delivery) {
            if (sertifier_delivery_check($delivery)) {
                $deliveryfilter[$delivery->id] = $delivery->title;
            }
        }

        if (optional_param('update', '', PARAM_INT)) {
            $updatingcert = true;
            $cmid = optional_param('update', '', PARAM_INT);
            $cm = get_coursemodule_from_id('sertifier', $cmid, 0, false, MUST_EXIST);
            $id = $cm->course;
            $course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
            $sertifiercertificate = $DB->get_record('sertifier', array('id' => $cm->instance), '*', MUST_EXIST);
            $recipients = $apirest->get_recipients($sertifiercertificate->deliveryid)->data->recipients;
        } else if (optional_param('course', '', PARAM_INT)) {
            $id = optional_param('course', '', PARAM_INT);
            $course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
        }

        $context = context_course::instance($course->id);
        $users = get_enrolled_users($context, "mod/sertifier:view", null, 'u.*');

        $quizchoices = array(0 => 'None');
        if ($quizes = $DB->get_records_select('quiz', 'course = :course_id', array('course_id' => $id) )) {
            foreach ($quizes as $quiz) {
                $quizchoices[$quiz->id] = $quiz->name;
            }
        }

        $mform =& $this->_form;
        $mform->addElement('hidden', 'course', $id);
        $mform->setType('course', PARAM_INT);
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('static',
            'overview',
            get_string('overview', 'sertifier'),
            get_string('activitydescription', 'sertifier'));
        if (!$updatingcert) {
            if (isset($_GET['deliveryId'])) {
                $mform->addElement('static', 'edit', '', get_string('createdDelivery', 'sertifier', $_GET['deliveryId']));
            } else {
                $newdelivery = array();
                $newdelivery[] =& $mform->createElement('text', 'deliveryName', "Delivery Name", ['style' => 'width: 296px']);
                $newdelivery[] =& $mform->createElement('submit',
                    'createDelivery',
                    get_string('create'),
                    ['style' => 'width: 100px']);
                $mform->addGroup($newdelivery, 'new_delivery', get_string('createDelivery', 'sertifier'), array(' '), false);
                $mform->setType('deliveryName', PARAM_TEXT);
            }

            if (count($deliveryfilter) > 0) {
                $mform->addElement('select',
                    'delivery',
                    get_string('selectedDelivery', 'sertifier'),
                    $deliveryfilter,
                    array('style' => 'width: 400px'));
                $mform->addRule('delivery', null, 'required', null, 'client');
            } else {
                $mform->addElement('static',
                    'delivery',
                    get_string('selectedDelivery', 'sertifier'),
                    get_string('notFoundDelivery', 'sertifier'));
            }
            if (isset($_GET['deliveryId']) && array_key_exists($_GET['deliveryId'], $deliveryfilter)) {
                $mform->setDefault('delivery', $_GET['deliveryId']);
            }
        } else {
            $deliveryname = $deliveryfilter[$sertifiercertificate->deliveryid];
            $mform->addElement('static',
                'selectdelivery',
                get_string('selectedDelivery', 'sertifier'),
                get_string('cantchangedelivery', 'sertifier',
                $deliveryname));
            $mform->addElement('hidden', 'delivery', $sertifiercertificate->deliveryid);
            $mform->setType('delivery', PARAM_TEXT);
        }

        $mform->addElement('text', 'name', get_string('activityname', 'sertifier'), array('style' => 'width: 400px'));
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', $course->fullname);

        $mform->addElement('header', 'chooseusers', get_string('selectRecHeader', 'sertifier'));
        $this->add_checkbox_controller(1, 'Select All/None');

        foreach ($users as $user) {
            if ($updatingcert) {
                $key = array_search($user->email, array_column($recipients, "email"));
                if ($key !== false) {
                    $link = "<a href='https://verified.cv/en/verify/" .
                        $recipients[$key]->certificateNo .
                        "' target='_blank'>View Credential</a>";
                    $label = $user->firstname . ' ' . $user->lastname . ' - ' . $user->email . ' - ' . $link;
                    $mform->addElement('advcheckbox', 'users['.$user->id.']', $label);
                    $mform->setDefault('users['.$user->id.']', 1);
                } else {
                    $label = $user->firstname . ' ' . $user->lastname . ' - ' . $user->email;
                    $mform->addElement('advcheckbox', 'users['.$user->id.']', $label, null, array('group' => 1));
                }
            } else {
                $label = $user->firstname . ' ' . $user->lastname . ' - ' . $user->email;
                $mform->addElement('advcheckbox', 'users['.$user->id.']', $label, null, array('group' => 1));
            }
        }

        $mform->addElement('header', 'gradeissue', get_string('gradeissueheader', 'sertifier'));
        $mform->addElement('select', 'finalquiz', get_string('chooseexam', 'sertifier'), $quizchoices);
        $mform->setType('finalquiz', PARAM_INT);
        $mform->addElement('text', 'passinggrade', get_string('passinggrade', 'sertifier'));
        $mform->setType('passinggrade', PARAM_INT);
        $mform->setDefault('passinggrade', 70);

        $mform->addElement('header', 'completionissue', get_string('completionissueheader', 'sertifier'));
        $mform->addElement('checkbox', 'completionactivities', get_string('completionissuecheckbox', 'sertifier'));
        $mform->setType('completionactivities', PARAM_TEXT);
        if ($updatingcert && isset($sertifiercertificate->completionactivities)) {
            $mform->setDefault('completionactivities', 1);
        }

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }
}
