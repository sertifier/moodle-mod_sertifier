<?php

defined('MOODLE_INTERNAL') || die;

// TODO - language tags
$settings->add(
	new admin_setting_configtext('sertifier_api_key', get_string('apikeylabel', 'sertifier'), get_string('apikeyhelp', 'sertifier'), '')
);
