<?php

/**
 * @since      08-Aug-2016
 * @package    local_akindi
 * @author     David Wolever <david@wolever.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

/**
 * This function extends the navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function local_akindi_extend_navigation_course($navigation, $course, $context) {
  global $CFG;
  if (has_capability('moodle/grade:edit', $context)) {
    $url = new moodle_url('/local/akindi/launch.php', array('id'=>$course->id));
    $navigation->add('Launch Akindi', $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/navigationitem', ''));
      
  }
}
