<?php

if ( $hassiteconfig ){
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
		bin2hex(random_bytes(16)),
		PARAM_TEXT
	));

        $customfields = profile_get_custom_fields();
        unset($options);
        $options['idnumber'] = "ID number";
        $options['userid'] = "Moodle user id";
        foreach ($customfields as $field) {
            $options[$field->shortname] = $field->name;
        }
        
        $settings->add(new admin_setting_configselect(
		'akindi_student_id_field',
		'Akindi student id field',
		'The field from a user profile to use as the student id on Akindi forms.',
		'idnumber',
		$options
	));
}
