<?php
require_once(realpath(dirname(__FILE__)).'/../../config.php');

function ak_sign($key, $to_sign) {
  if (!$key)
    throw new moodle_exception("empty-signing-key", "akindi");
  return base64_encode(hash_hmac("sha1", $to_sign, trim($key), $raw_output=TRUE));
}

function ak_get($obj, $attr, $default=null) {
  if (!property_exists($obj, $attr)) {
    if ($default !== null)
      return $default;
    throw new moodle_exception("invalid-attr:$attr", "akindi");
  }
  return $obj->{$attr};
}

function ak_load_action($action) {
  $res = json_decode($action);
  if (!$res)
    throw new moodle_exception("invalid-action-json", "akindi");
  return $res;
}

function ak_random_bytes($n) {
  $res = "";
  while ($n > 0) {
    $res .= chr(rand(0, 255));
    $n -= 1;
  }
  return $res;
}

?>
