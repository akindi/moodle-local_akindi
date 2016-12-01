<?php

define('AJAX_SCRIPT', true);
define('NO_MOODLE_COOKIES', true);

require_once('./common.php');

require_once("$CFG->libdir/gradelib.php");
require_once("$CFG->dirroot/grade/querylib.php");

require_once("$CFG->dirroot/user/profile/lib.php");

function ak_get_validate_course_id($action) {
  global $AK_USER_ID;

  $course_id = ak_get($action, 'course_id');
  $context = context_course::instance($course_id);
  if (!has_capability('moodle/grade:edit', $context, $user=$AK_USER_ID))
    throw new moodle_exception("no-permission:$AK_USER_ID-in-$course_id", 'akindi');
  return $course_id;
}

function ak_action_get_roster($action) {
  global $CFG;
  
  $course_id = ak_get_validate_course_id($action);
  $context = context_course::instance($course_id);

  $students = array();
  foreach (get_enrolled_users($context) as $id=>$student) {
//    file_put_contents("/tmp/akindi.log", "akindi_student_id_field: ".$CFG->akindi_student_id_field."\n");
    if ($CFG->akindi_student_id_field == "idnumber") {
        $idnumber = $student->idnumber;
    } else if ($CFG->akindi_student_id_field == "userid") {
        $idnumber = $id;
    } else {
        // Use a custom profile field for the idnumber
        profile_load_custom_fields($student);
        $idnumber = $student->profile[$CFG->akindi_student_id_field];

        // Remove any characters that are not 0-9.
        $idnumber = preg_replace("/[^0-9]/", "", $idnumber);
    }
    
    array_push($students, array(
      'student_id'=>$idnumber,
      'name'=>$student->lastname . "; " . $student->firstname,
      'fields'=>array(
        'lms_user_id'=>$student->id,
        'lms_email'=>$student->email,
        'lms_username'=>$student->username,
      )
    ));
  }

  return array(
    'sections'=>array(),
    'students'=>$students,
  );
}

function ak_action_list_grade_items($action) {
  $course_id = ak_get_validate_course_id($action);
  $result = array();
  foreach (grade_item::fetch_all(array('courseid'=>$course_id, 'itemtype'=>'manual')) as $item) {
    array_push($result, array(
      'id'=>$item->id,
      'name'=>$item->itemname,
      'min_mark'=>$item->grademin,
      'max_mark'=>$item->grademax,
      'hidden'=>(bool)$item->hidden,
    ));
  }
  return $result;
}

function ak_action_create_update_grade_item($action) {
  global $DB;
  $course_id = ak_get_validate_course_id($action);
  $obj = ak_get($action, 'item');
  $item_id = ak_get($obj, 'id', 0);
  $item = new grade_item(array('id'=>$item_id, 'courseid'=>$course_id));
  grade_item::set_properties($item, array(
    'courseid'=>$course_id,
    'itemtype'=>'manual',
    'itemmodule'=>NULL,
    'gradetype'=>GRADE_TYPE_VALUE,
    'itemname'=>ak_get($obj, 'name'),
    'grademin'=>ak_get($obj, 'min_mark'),
    'grademax'=>ak_get($obj, 'max_mark'),
    'hidden'=>ak_get($obj, 'hidden'),
  ));
  if ($item_id) {
    $item->update();
  } else {
    $item->itemnumber = 0;
    $item->insert();
  }
  return array(
    'id'=>$item->id,
    'name'=>$item->itemname,
    'min_mark'=>$item->grademin,
    'max_mark'=>$item->grademax,
    'hidden'=>(bool)$item->hidden,
    'moodle_item'=>$item,
  );
}

function ak_action_update_grades($action) {
  global $USER;
  global $AK_POST_JSON;
  $course_id = ak_get_validate_course_id($action);
  $item_id = ak_get($action, 'item_id');

  if (!$grade_items = grade_item::fetch_all(array('courseid'=>$course_id, 'id'=>$item_id)))
    throw new moodle_exception("no-such-grade-item", "akindi");

  if (count($grade_items) != 1)
    throw new moodle_exception("multiple-grade-items", "akindi");

  $grade_item = reset($grade_items);
  unset($grade_items);
  $grade_item->itemtype = 'mod';
  $grade_item->itemmodule = 'assign';

  $res = array();
  foreach (ak_get($AK_POST_JSON, 'updates') as $update) {
    $userid = ak_get($update, 'lms_user_id');
    $rawgrade = ak_get($update, 'mark');
    $did_update = $grade_item->update_raw_grade(
      $userid, $rawgrade, 'akindi', '', FORMAT_PLAIN, $USER->id
    );
    array_push($res, array('lms_user_id'=>$userid, 'success'=>$did_update));
  }
  return $res;
}

function ak_action_selftest($action) {
  global $AK_POST_JSON;
  return array('success'=>true, 'body'=>$AK_POST_JSON);
}

function ak_run() {
  global $CFG;
  global $AK_USER_ID;
  global $AK_POST_JSON;

  $user_id = required_param('ak_key', PARAM_RAW);
  $sig = required_param('ak_signature', PARAM_RAW);
  $expires = required_param('ak_expires', PARAM_RAW);
  $action_str = required_param('action', PARAM_RAW);

  $is_post = $_SERVER['REQUEST_METHOD'] === 'POST';
  $post_data_str = $is_post? file_get_contents('php://input') : null;

  $user_key = ak_sign($CFG->akindi_instance_secret, $user_id);
  $str_to_sign = "$expires\n{$_SERVER['REQUEST_METHOD']}\n$action_str";
  if ($is_post)
    $str_to_sign = "$str_to_sign\n" . ($post_data_str? $post_data_str : "");
  $expected_sig = ak_sign($user_key, $str_to_sign);

  if ($expected_sig !== $sig)
    throw new moodle_exception('bad-signature', 'akindi');

  $expired_ago = time() - floatval($expires);
  if ($expired_ago > 0)
    throw new moodle_exception("signature-expired:$expired_ago", "akindi");

  $AK_USER_ID = $user_id;
  $AK_POST_JSON = $post_data_str? json_decode($post_data_str) : null;
  if ($is_post && $post_data_str && !$AK_POST_JSON)
    throw new moodle_exception("invalid-post-json", "akindi");
  $action = ak_load_action($action_str);
  $func_name = "ak_action_" . ak_get($action, 'name');
  if (!function_exists($func_name))
    throw new moodle_exception("unknown-action:" . $action->name, "akindi");
  return call_user_func($func_name, $action);
}

$cpm = core_plugin_manager::instance();
$akindi_plugin = $cpm->get_plugins_of_type('local')['akindi'];
header("X-Ak-Plugin-Release: " . $akindi_plugin->versiondisk);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Max-Age: 86400");

$result = json_encode(ak_run());
echo $result;

?>
