#!/usr/bin/php
<?php

include("config.php");
include("includes/functions.php");

if(!$config['enable_syslog']) { echo("Syslog support disabled.\n"); exit(); }

$add = 0;
$discard = 0;
$total = 0;

mysql_query("DELETE FROM `syslog` WHERE `msg` LIKE '%last message repeated%'");
mysql_query("DELETE FROM `syslog` WHERE `msg` LIKE '%Connection from UDP: [89.21.224.44]:%'");
mysql_query("DELETE FROM `syslog` WHERE `msg` LIKE '%Connection from UDP: [89.21.224.35]:%'");

$q = mysql_query("SELECT * FROM `syslog` where `processed` = '0'");
while($entry = mysql_fetch_array($q)){

  unset($device_id);
  unset($maybehost);
  unset($perhapshost);

  $device_id_host = @mysql_result(mysql_query("SELECT device_id FROM devices WHERE `hostname` = '".$entry['host']."'"),0);

  if($device_id_host) { 
    $device_id = $device_id_host;
  } else {
    $device_id_ip = @mysql_result(mysql_query("SELECT D.device_id as device_id FROM ipaddr AS A, interfaces AS I, devices AS D WHERE A.addr = '" . $entry['host']."' AND I.interface_id = A.interface_id AND D.device_id = I.device_id"),0);
    if($device_id_ip) { 
      $device_id = $device_id_ip;
    }
  } 

  if($device_id) {

    if(mysql_result(mysql_query("SELECT `os` FROM `devices` WHERE `device_id` = '$device_id'"),0) == "IOS") {
      list(,$entry[msg]) = split(": %", $entry['msg']);
      $entry['msg'] = "%" . $entry['msg'];
      $entry['msg'] = preg_replace("/^%(.+?):\ /", "\\1||", $entry['msg']);
      list($entry['program'], $entry['msg']) = explode("||", $entry['msg']);
    } else {
      $program = addslashes($entry['program']);
      $entry['msg'] = preg_replace("/^$program:\ /", "", $entry['msg']);
      if(preg_match("/^[a-zA-Z\/]+\[[0-9]+\]:/", $entry['msg'])) {
        $entry['msg'] = preg_replace("/^(.+?)\[[0-9]+\]:\ /", "\\1||", $entry['msg']);
        list($entry['program'], $entry['msg']) = explode("||", $entry['msg']);
	echo("fix! -> " . $entry['program'] . " -> " . $entry['msg'] . "\n");
      }
    }

    $x  = "UPDATE `syslog` set `device_id` = '$device_id', `program` = '".$entry['program']."', `msg` = '" . mysql_real_escape_string($entry['msg']) . "', processed = '1' WHERE `seq` = '" . $entry['seq'] . "'";
#    echo("$x \n");
    mysql_query($x);
    unset ($fix);
    $add++;
  } else {
     echo("Failed entry from '" . $entry['host'] . "'");
     $x = "DELETE FROM `syslog` where `seq` = '" . $entry['seq'] . "'"; 
     mysql_query($x);
     $discard++; 
  }

  $total++;

}

#echo("$total records processed: $add added to database, $discard discarded");

?>
