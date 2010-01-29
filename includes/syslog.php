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
    $entry['device_id'] = $device_id_host;
  } else {
    $device_id_ip = @mysql_result(mysql_query("SELECT device_id FROM ipv4_addresses AS A, interfaces AS I WHERE 
    A.ipv4_address = '" . $entry['host']."' AND I.interface_id = A.interface_id"),0);

    echo("SELECT device_id FROM ipv4_addresses AS A, interfaces AS I WHERE
    A.ipv4_address = '" . $entry['host']."' AND I.interface_id = A.interface_id");

    if($device_id_ip) { 
      $entry['device_id'] = $device_id_ip;
    }
  } 

  print_r($entry);

  if($entry['device_id'] && !$delete) {
    $os = mysql_result(mysql_query("SELECT `os` FROM `devices` WHERE `device_id` = '".$entry['device_id']."'"),0);
    if($os == "ios" || $os == "iosxe") {
      if(strstr($entry[msg], "%")) {
        $entry['msg'] = preg_replace("/^%(.+?):\ /", "\\1||", $entry['msg']);
        list(,$entry[msg]) = split(": %", $entry['msg']);
        $entry['msg'] = "%" . $entry['msg'];
        $entry['msg'] = preg_replace("/^%(.+?):\ /", "\\1||", $entry['msg']);      
      } else { 
        $entry['msg'] = preg_replace("/^.*[0-9]:/", "", $entry['msg']);
        $entry['msg'] = preg_replace("/^[0-9][0-9]\ [A-Z]{3}:/", "", $entry['msg']);
        $entry['msg'] = preg_replace("/^(.+?):\ /", "\\1||", $entry['msg']);
        #$entry['msg'] = "||" . $entry['msg'];
      }

      $entry['msg'] = preg_replace("/^.+\.[0-9]{3}:/", "", $entry['msg']);
      $entry['msg'] = preg_replace("/^.+-Traceback=/", "Traceback||", $entry['msg']);

      list($entry['program'], $entry['msg']) = explode("||", $entry['msg']);
      $entry['msg'] = preg_replace("/^[0-9]+:/", "", $entry['msg']);

      if(!$entry['program']) {
         $entry['msg'] = preg_replace("/^([0-9A-Z\-]+?):\ /", "\\1||", $entry['msg']);
	 list($entry['program'], $entry['msg']) = explode("||", $entry['msg']);
      }

      if(!$entry['msg']) { $entry['msg'] = $entry['program']; unset ($entry['program']); }

    } else {
      $program = preg_quote($entry['program'],'/');
      $entry['msg'] = preg_replace("/^$program:\ /", "", $entry['msg']);
      if(preg_match("/^[a-zA-Z\/]+\[[0-9]+\]:/", $entry['msg'])) {
        $entry['msg'] = preg_replace("/^(.+?)\[[0-9]+\]:\ /", "\\1||", $entry['msg']);
        list($entry['program'], $entry['msg']) = explode("||", $entry['msg']);
      }
    }
    $x  = "UPDATE `syslog` set `device_id` = '".$entry['device_id']."', `program` = '".$entry['program']."', `msg` = '" . mysql_real_escape_string($entry['msg']) . "', processed = '1' WHERE `seq` = '" . $entry['seq'] . "'";
    $entry['processed'] = 1;
    if($update) { mysql_query($x); echo($x); }
    unset ($fix);
  } else {
     $x = "DELETE FROM `syslog` where `seq` = '" . $entry['seq'] . "'"; 
     if($update) { mysql_query($x);}

     $entry['deleted'] = '1';

  }

  return $entry; 

}


?>
