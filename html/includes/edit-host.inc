<?

  $descr = $_POST['descr'];
  $ignore = $_POST['ignore'];
  $type = $_POST['type'];
  $disabled = $_POST['disabled'];

  $sql = "UPDATE `devices` SET `purpose` = '$descr', `type` = '$type', `ignore` = '$ignore',  `disabled` = '$disabled' WHERE `device_id` = '$_GET[id]'";
  $query = mysql_query($sql);

  $rows_updated = mysql_affected_rows();

  if($rows_updated > 0) {
    $update_message = mysql_affected_rows() . " Device record updated.";
    $updated = 1;
  } elseif ($rows_updated = '-1') {
    $update_message = "Device record unchanged. No update necessary.";
    $updated = -1;
  } else {
    $update_message = "Device record update error.";
    $updated = 0;
  }

?>
