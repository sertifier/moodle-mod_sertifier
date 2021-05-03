<?php

$capabilities = array(

  'mod/sertifier:addinstance' => array(
      'riskbitmask' => RISK_XSS,
      'captype' => 'write',
      'contextlevel' => CONTEXT_COURSE,
      'archetypes' => array(
          'editingteacher' => CAP_ALLOW,
          'manager' => CAP_ALLOW
      ),
      'clonepermissionsfrom' => 'moodle/course:manageactivities'
  ),

  'mod/sertifier:view' => array(

      'captype' => 'read',
      'contextlevel' => CONTEXT_MODULE,
      'archetypes' => array(
          'student' => CAP_ALLOW,
          'teacher' => CAP_ALLOW,
          'editingteacher' => CAP_ALLOW,
          'manager' => CAP_ALLOW
      ),
      'clonepermissionsfrom' => 'mod/page:view'
  ),

  'mod/sertifier:manage' => array(

      'captype' => 'read',
      'contextlevel' => CONTEXT_MODULE,
      'archetypes' => array(
          'teacher' => CAP_ALLOW,
          'editingteacher' => CAP_ALLOW,
          'manager' => CAP_ALLOW
      )
  ),

);