#!/usr/bin/php
<?php
include("config.php");
include("includes/functions.php");
  
$sql = "SELECT * FROM devices AS D, services AS S WHERE D.status = '1' AND S.service_host = D.device_id ORDER by D.device_id DESC";
$query = mysql_query($sql);
while ($service = mysql_fetch_array($query)) {

  unset($check, $service_status, $time, $status);

  $service_status = $service['service_status'];

  $service_type = strtolower($service[service_type]);

  $service_param = $service['service_param'];

  $checker_script = "includes/services/" . $service_type . "/check.inc";

  if(is_file($checker_script)) {
    include($checker_script);
  } else {
    $status = "2";
    $check = "Error : Script not found ($checker_script)";
  }
  if($service_status != $status) { $updated = ", `service_changed` = '" . time() . "' "; } else { unset($updated); }
  mysql_query("UPDATE `services` SET `service_status` = '$status', `service_message` = '$check', `service_checked` = '" . time() . "' $updated WHERE `service_id` = '$service[service_id]'");

}
?>
