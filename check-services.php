#!/usr/bin/env php
<?php

chdir(dirname($argv[0]));

include("includes/defaults.inc.php");
include("config.php");
include("includes/functions.php");

foreach (dbFetchRows("SELECT * FROM `devices` AS D, `services` AS S WHERE S.device_id = D.device_id ORDER by D.device_id DESC") as $service)
{
  if ($service['status'] = "1")
  {
    unset($check, $service_status, $time, $status);
    $service_status = $service['service_status'];
    $service_type = strtolower($service['service_type']);
    $service_param = $service['service_param'];
    $checker_script = $config['install_dir'] . "/includes/services/" . $service_type . "/check.inc";

    if (is_file($checker_script))
    {
      include($checker_script);
    }
    else
    {
      $status = "2";
      $check = "Error : Script not found ($checker_script)";
    }

    $update = array();

    if ($service_status != $status)
    {
      $update['service_changed'] = time();

      if ($service['sysContact']) { $email = $service['sysContact']; } else { $email = $config['email_default']; }
      if ($status == "1")
      {
        $msg  = "Service Up: " . $service['service_type'] . " on " . $service['hostname'];
        notify($device, "Service Up: " . $service['service_type'] . " on " . $service['hostname'], $msg);
      }
      elseif ($status == "0")
      {
        $msg  = "Service Down: " . $service['service_type'] . " on " . $service['hostname'];
        notify($device, "Service Down: " . $service['service_type'] . " on " . $service['hostname'], $msg);
      }
    } else { unset($updated); }

    $update = array_merge(array('service_status' => $status, 'service_message' => $check, 'service_checked' => time()), $update);
    dbUpdate($update, 'services', '`service_id` = ?', array($service['service_id']));
    unset($update);

  } else {
    $status = "0";
  }

  $rrd  = $config['rrd_dir'] . "/" . $service['hostname'] . "/" . safename("service-" . $service['service_type'] . "-" . $service['service_id'] . ".rrd");

  if (!is_file($rrd))
  {
    rrdtool_create ($rrd, "DS:status:GAUGE:600:0:1 ".$config['rrd_rra']);
  }
  if ($status == "1" || $status == "0")
  {
    rrdtool_update($rrd,"N:".$status);
  } else {
    rrdtool_update($rrd,"N:U");
  }

} # while

?>
