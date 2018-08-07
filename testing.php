<?php
/*
 * This file is intended to be used along side Akindi's test suite to exercise
 * the functionality of the Moodle plugin and Akindi's communication with the
 * plugin.
 */
getenv("AK_MOODLE_TEST") || die("ERROR: must be run from a testing environment (AK_MOODLE_TEST)");

define('AJAX_SCRIPT', true);
define('NO_MOODLE_COOKIES', true);

require_once(realpath(dirname(__FILE__)).'/common.php');

require_once("$CFG->libdir/gradelib.php");
require_once("$CFG->dirroot/grade/querylib.php");

$CFG->debug = 32767;
$CFG->debugdisplay = 1;

function ak_test_insert($table, $attrs) {
  global $DB;
  $obj = new StdClass();
  foreach ($attrs as $name=>$val)
    $obj->{$name} = $val;
  $obj->id = $DB->insert_record($table, $obj);
  return $obj;
}

function ak_test_insert_user($num) {
  return ak_test_insert('user', array(
    'auth'=>'manual',
    'confirmed'=>1,
    'mnethostid'=>1,
    'email'=>"aktest$num@example.com",
    'username'=>"aktest$num",
    'lastname'=>"Test User $num",
    'firstname'=>"Test User $num",
    'idnumber'=>"$num",
  ));
}

function ak_test_enrol($enrol, $context, $role, $user) {
  ak_test_insert('user_enrolments', array(
    'enrolid'=>$enrol->id,
    'status'=>'0',
    'userid'=>$user->id,
    'timestart'=>time(),
    'timeend'=> '0',
    'modifierid'=>0,
    'timecreated'=>time(),
    'timemodified'=>time(),
  ));

  ak_test_insert('role_assignments', array(
    'roleid'=>$role,
    'contextid'=>$context->id,
    'userid'=>$user->id,
    'component'=>'',
    'itemid'=>0,
    'timemodified'=>time(),
    'modifierid'=>0,
  ));
}

function ak_test_action_setup() {
  global $DB;
  global $CFG;
  $DB->delete_records_select('grade_items', "itemname like 'aktest%'");
  $DB->delete_records_select('user', "username like 'aktest%'");
  $DB->delete_records_select('course', "shortname like 'aktest%'");
  $course = ak_test_insert('course', array(
    'fullname'=>'test course',
    'shortname'=>'aktest01',
  ));
  $enrol = ak_test_insert('enrol', array('enrol'=>'manual', 'courseid'=>$course->id));
  $students = array(
    ak_test_insert_user(1),
    ak_test_insert_user(2),
    ak_test_insert_user(3),
  );

  $context = context_course::instance($course->id);
  $studentroleid = $DB->get_field('role', 'id', array('shortname' => 'student'));
  foreach ($students as $user) {
    ak_test_enrol($enrol, $context, $studentroleid, $user);
  }

  $manager = ak_test_insert_user("mgr");
  $mgrroleid = $DB->get_field('role', 'id', array('shortname' => 'manager'));
  ak_test_enrol($enrol, $context, $mgrroleid, $manager);

  $item = new grade_item(array('courseid'=>$course->id));
  grade_item::set_properties($item, array(
    'courseid'=>$course->id,
    'itemtype'=>'manual',
    'itemmodule'=>NULL,
    'gradetype'=>GRADE_TYPE_VALUE,
    'itemname'=>'aktest-item',
    'grademin'=>0,
    'grademax'=>10,
    'hidden'=>0,
    'itemnumber'=>0,
  ));
  $item->insert();

  return array(
    'course'=>$course,
    'students'=>$students,
    'user_id'=>$manager->id,
    'grade_item'=>$item,
    'user_key'=>ak_sign($CFG->akindi_instance_secret, $manager->id),
  );
}

function ak_test_action_get_grades() {
  global $DB;
  $cleaned_itemid = required_param($_GET["itemid"], PARAM_INT);
  return $DB->get_records('grade_grades',array('itemid'=>$cleaned_itemid));
}

function ak_test_run() {
  $action = required_param('action', PARAM_RAW);
  $func_name = "ak_test_action_$action";
  if (!function_exists($func_name))
    throw new moodle_exception("unknown-action:" . $action->name, "akindi");
  return call_user_func($func_name);
}

$result = json_encode(ak_test_run());
echo $result;

?>
