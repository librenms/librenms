<?php

$descr = mres($_POST['descr']);
$ignore = mres($_POST['ignore']);
$type = mres($_POST['type']);
$disabled = mres($_POST['disabled']);
$community = mres($_POST['community']);
$snmpver = mres($_POST['snmpver']);
$port = mres($_POST['port']);
$timeout = mres($_POST['timeout']);
$retries = mres($_POST['retries']);

#FIXME needs more sanity checking! and better feedback

$sql = "UPDATE `devices` SET `purpose` = '" . $descr . "', `community` = '" . $community . "', `type` = '$type'";
$sql .= ", `snmpver` = '" . $snmpver . "', `ignore` = '$ignore',  `disabled` = '$disabled', `port` = '$port', ";
$sql .= "`timeout` = '$timeout', `retries` = '$retries' WHERE `device_id` = '".$device['device_id']."'";
$query = mysql_query($sql);

$rows_updated = mysql_affected_rows();

if ($rows_updated > 0)
{
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