<?php

if ( $hassiteconfig ){
  require_once(realpath(dirname(__FILE__)).'/common.php');
  require_once(realpath(dirname(__FILE__)).'/lib.php');

  $settings = new admin_settingpage('local_akindi', 'Akindi Settings');
  $ADMIN->add('localplugins', $settings);

  $settings->add(new admin_setting_configtext(
    'akindi_launch_url',
    'Akindi launch URL',
    'The URL used to launch Akindi.',
    'https://akindi.com/api/moodle/launch',
    PARAM_TEXT
  ));

  $settings->add(new admin_setting_configtext(
    'akindi_public_key',
    'Akindi public key',
    'The public key given to you by Akindi.',
    '',
    PARAM_TEXT
  ));

  $settings->add(new admin_setting_configtext(
    'akindi_secret_key',
    'Akindi secret key',
    'The secret key given to you by Akindi.',
    '',
    PARAM_TEXT
  ));

  $settings->add(new admin_setting_configtext(
    'akindi_instance_secret',
    'Akindi instance secret',
    'A secret key you have generated (the default value is suitable). DO NOT share this value with Akindi.',
    bin2hex(ak_random_bytes(16)),
    PARAM_TEXT
  ));

  $settings->add(new admin_setting_configselect(
    'akindi_student_id_field',
    'Akindi student ID field',
    'The user profile field Akindi should use as a numeric student ID on bubble sheets.',
    'idnumber',
    ak_settings_get_student_id_options()
  ));

  $settings->add(new admin_setting_configcheckbox(
    'akindi_open_in_new_window',
    'Open in new window',
    'Opens Akindi in a new window. Note: users will get a "popup blocked" warning which they will need to disable.',
    false,
    PARAM_BOOL
  ));
}

?>
