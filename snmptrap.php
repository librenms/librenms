#!/usr/bin/env php
<?php

  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  ini_set('log_errors', 1);
  ini_set('error_reporting', E_ALL);

  include("includes/defaults.inc.php");
  include("config.php");
  include("includes/functions.php");

  $entry = explode(",", $argv[1]);

  print_r($entry);

  $device = @mysql_fetch_array(mysql_query("SELECT * FROM devices WHERE `hostname` = '".$entry['0']."'"));

  if(!$device['device_id']) {
    $device = @mysql_fetch_array(mysql_query("SELECT * FROM ipv4_addresses AS A, ports AS I WHERE
    A.ipv4_address = '" . $entry['0']."' AND I.interface_id = A.interface_id"));
  }
  if(!$device['device_id']) { exit; } else { }
  
  print_r($device);

  $file = $config['install_dir'] . "/includes/snmptrap/".$entry['1'].".inc.php";
  if(is_file($file)) { include("$file"); } else { echo("unknown trap ($file)"); exit; }


?>
