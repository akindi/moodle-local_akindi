<?php

/**
 * @since      08-Aug-2016
 * @package    local_akindi
 * @author     David Wolever <david@wolever.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once("$CFG->dirroot/user/profile/lib.php");

/**
 * Extends course navigation with the Akindi link (Moodle 3.X)
 */
function local_akindi_extend_navigation_course($navigation, $course, $context) {
  global $CFG;
  if (!has_capability('moodle/grade:edit', $context))
    return;

  $url = new moodle_url('/local/akindi/launch.php', array('id'=>$course->id));
  $navigation->add('Launch Akindi', $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('akindi-icon', 'Launch Akindi', 'local_akindi'));
}

/**
 * Extends course navigation with the Akindi link (Moodle 2.X)
 */
function local_akindi_extends_settings_navigation($navigation, $context) {
  global $PAGE;
  if (!has_capability('moodle/grade:edit', $context))
    return;

  $settingnode = $navigation->find('courseadmin', navigation_node::TYPE_COURSE);
  if (!$settingnode)
    return;

  $settingnode->add_node(navigation_node::create(
    'Launch Akindi',
    new moodle_url('/local/akindi/launch.php', array('id'=>$PAGE->course->id)),
    navigation_node::NODETYPE_LEAF,
    'akindi',
    'null',
    new pix_icon('akindi-icon', 'Launch Akindi', 'local_akindi')
  ));
}

/**
 * Returns the possible fields used for student ID numbers.
 */
function ak_settings_get_student_id_options() {
  $options = array(
    'idnumber'=>"ID number (idnumber)",
    'userid'=>"Moodle user id (userid)",
  );
  $customfields = profile_get_custom_fields();
  foreach ($customfields as $field) {
    $options[$field->shortname] = "{$field->name} ({$field->shortname})";
  }
  return $options;
}
