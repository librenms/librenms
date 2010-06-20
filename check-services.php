#!/usr/bin/php
<?php
include("includes/defaults.inc.php");
include("config.php");
include("includes/functions.php");
  
$sql = "SELECT * FROM devices AS D, services AS S WHERE S.device_id = D.device_id ORDER by D.device_id DESC";
$query = mysql_query($sql);
while ($service = mysql_fetch_array($query)) {

 if($service['status'] = "1") {
  unset($check, $service_status, $time, $status);
  $service_status = $service['service_status'];
  $service_type = strtolower($service['service_type']);
  $service_param = $service['service_param'];
  $checker_script = "includes/services/" . $service_type . "/check.inc";
  if(is_file($checker_script)) {
    include($checker_script);
  } else {
    $status = "2";
    $check = "Error : Script not found ($checker_script)";
  }
  if($service_status != $status) { 
    $updated = ", `service_changed` = '" . time() . "' "; 
    if($service['sysContact']) { $email = $service['sysContact']; } else { $email = $config['email_default']; }
    if($status == "1") {
        $msg  = "Service Up: " . $service['service_type'] . " on " . $service['hostname'];
        $msg .= " at " . date($config['timestamp_format']);
	mail($email, "Service Up: " . $service['service_type'] . " on " . $service['hostname'], $msg, $config['email_headers']);
    } elseif ($status == "0") {
	$msg  = "Service Down: " . $service['service_type'] . " on " . $service['hostname'];
        $msg .= " at " . date($config['timestamp_format']);
        mail($email, "Service Down: " . $service['service_type'] . " on " . $service['hostname'], $msg, $config['email_headers']);
    }

  } else { unset($updated); }
  $update_sql = "UPDATE `services` SET `service_status` = '$status', `service_message` = '" . addslashes($check) . "', `service_checked` = '" . time() . "' $updated WHERE `service_id` = '" . $service['service_id']. "'";
  mysql_query($update_sql);
 } else {
   $status = "0";
 }
  
  
  $rrd  = $config['rrd_dir'] . "/" . $service['hostname'] . "/" . safename("service-" . $service['service_type'] . "-" . $service['service_id'] . ".rrd");

  if (!is_file($rrd)) {
    $create = $config['rrdtool'] . " create $rrd \
     --step 300 \
     DS:status:GAUGE:600:0:1 \
     RRA:AVERAGE:0.5:1:1200 \
     RRA:AVERAGE:0.5:12:2400";
    shell_exec($create);
  }

  if($status = "1" || $status = "0") {
    rrdtool_update($rrd,"N:".$status);
  }
}
?>
