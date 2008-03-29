<?php

function process_syslog ($entry, $update) {

  global $config;

  foreach($config['syslog_filter'] as $bi) {
    if (strstr($entry['msg'], $bi)) {
        $delete = 1;
    }
  }

  $device_id_host = @mysql_result(mysql_query("SELECT device_id FROM devices WHERE `hostname` = '".$entry['host']."'"),0);

  if($device_id_host) { 
    $device_id = $device_id_host;
  } else {
    $device_id_ip = @mysql_result(mysql_query("SELECT D.device_id as device_id FROM ipaddr AS A, interfaces AS I, devices AS D WHERE A.addr = '" . $entry['host']."' AND I.interface_id = A.interface_id AND D.device_id = I.device_id"),0);
    if($device_id_ip) { 
      $device_id = $device_id_ip;
    }
  } 

  if($device_id && !$delete) {
    $entry['device_id'] = $device_id;
    if(mysql_result(mysql_query("SELECT `os` FROM `devices` WHERE `device_id` = '$device_id'"),0) == "IOS") {
      list(,$entry[msg]) = split(": %", $entry['msg']);
      $entry['msg'] = "%" . $entry['msg'];
      $entry['msg'] = preg_replace("/^%(.+?):\ /", "\\1||", $entry['msg']);
      list($entry['program'], $entry['msg']) = explode("||", $entry['msg']);
    } else {
      $program = preg_quote($entry['program'],'/');
      $entry['msg'] = preg_replace("/^$program:\ /", "", $entry['msg']);
      if(preg_match("/^[a-zA-Z\/]+\[[0-9]+\]:/", $entry['msg'])) {
        $entry['msg'] = preg_replace("/^(.+?)\[[0-9]+\]:\ /", "\\1||", $entry['msg']);
        list($entry['program'], $entry['msg']) = explode("||", $entry['msg']);
      }
    }

    $x  = "UPDATE `syslog` set `device_id` = '$device_id', `program` = '".$entry['program']."', `msg` = '" . mysql_real_escape_string($entry['msg']) . "', processed = '1' WHERE `seq` = '" . $entry['seq'] . "'";
    $entry['processed'] = 1;
    if($update) { mysql_query($x); }
    unset ($fix);
  } else {
     $x = "DELETE FROM `syslog` where `seq` = '" . $entry['seq'] . "'"; 
     if($update) { mysql_query($x);}

     $entry['deleted'] = '1';

  }

  return $entry; 

}


?>
