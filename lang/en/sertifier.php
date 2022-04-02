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
 * Language strings for the sertifier module
 *
 * @package    mod_sertifier
 * @copyright  Sertifier <hr@sertifier.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Sertifier certificates & badges';

$string['modulename'] = 'Sertifier certificates & badges';
$string['modulename_help'] = 'Help Text';
$string['apikeylabel'] = 'API key';
$string['apikeyhelp'] = 'Enter your API key from app.sertifier.com';
$string['activityname'] = 'Activity name';
$string['selectRecHeader'] = 'Select Recipients';
$string['gradeissueheader'] = 'Auto-issue criteria: by final quiz grade';
$string['chooseexam'] = 'Choose final quiz';
$string['passinggrade'] = 'Percentage grade needed to pass course (%)';
$string['completionissueheader'] = 'Auto-issue criteria: by course completion';
$string['completionissuecheckbox'] = 'Yes, issue upon course completion';
$string['overview'] = 'Overview';
$string['existcertificate'] = 'Please use the button below to view your credential.';
$string['nonexistcertificate'] = 'You have not earned this credential yet.';
$string['viewcredential'] = 'View Credential';
$string['viewmanagementdesc'] = 'You can view the recipients who currently have an active credential below. For more details about the credentials, please use the button to go to the reports page in the Sertifier app.';
$string['gotoreports'] = 'Go to the reports';
$string['credentialNo'] = 'Credential No';
$string['name'] = 'Recipient Name';
$string['email'] = 'Recipient Email';
$string['issueDate'] = 'Issue Date';
$string['selectedDelivery'] = 'Selected Delivery:';
$string['activitydescription'] = 'Select a previously configured Delivery or create a new one for the credentials that are going to be issued.</br></br>The list you can select from will only contain Deliveries created from this interface and have the “Active” status.</br></br>Once you have created and prepared one, you will be able to use it again for multiple Moodle activities. However, you can not edit a Delivery that has issued credentials.</br></br>For managing your issuing process please visit <a href="https://app.sertifier.com" target="_blank">https://app.sertifier.com</a>.';
$string['cantchangedelivery'] = 'Delivery cannot be changed. Selected delivery: <b>{$a}</b>';
$string['createdDelivery'] = 'New delivery has been created. After activating, refresh the page. <a href="https://app.sertifier.com/en/home/send/{$a}" target="_blank">Activate Delivery</a>';
$string['createDelivery'] = "Create new delivery";
$string['notFoundDelivery'] = 'You don’t have any active delivery yet.';
$string['modulenameplural'] = 'Sertifier certificates/badges';
$string['datecreated'] = 'Date created';
$string['indexheader'] = 'All certificates/badges for {$a}';
$string['nocertificates'] = 'There are no certificates/badges';

$string['privacy:metadata:sertifier'] = 'In order to integrate with sertifier, user data needs to be exchanged with that service.';
$string['privacy:metadata:sertifier:fullname'] = 'Your full name is sent to the remote system to create credentials.';
$string['privacy:metadata:sertifier:firstname'] = 'Your email is sent to the remote system to send credentials by email.';
