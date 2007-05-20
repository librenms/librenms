#!/usr/bin/php
<?php

include("config.php");
include("includes/functions.php");

$add = 0;
$discard = 0;
$total = 0;


mysql_query("DELETE FROM `logs` WHERE `msg` LIKE '%Connection from UDP: [89.21.224.44]:%'");
mysql_query("DELETE FROM `logs` WHERE `msg` LIKE '%Connection from UDP: [89.21.224.35]:%'");

$q = mysql_query("SELECT * FROM `logs`");
while($l = mysql_fetch_array($q)){

  unset($host);
  unset($maybehost);
  unset($perhapshost);

  $maybehost = @mysql_result(mysql_query("SELECT D.device_id as device_id FROM ipaddr AS A, interfaces AS I, devices AS D WHERE A.addr = '" . $l['host'] . "' AND I.interface_id = A.interface_id AND D.device_id = I.device_id"),0);
  $perhapshost = @mysql_result(mysql_query("SELECT device_id FROM devices WHERE `hostname` = '$l[host]'"),0);

  if($maybehost) { 
    $host = $maybehost;
  } elseif($perhapshost) {
    $host = $perhapshost;
  }

  if($host) {

    if(mysql_result(mysql_query("SELECT os FROM `devices` WHERE `device_id` = '$host'"),0) == "IOS") {
      list(,$l[msg]) = split(": %", $l[msg]);
      $l[msg] = "%" . $l[msg];
      $l[msg] = preg_replace("/^%(.+):\ /", "\\1||", $l[msg]);
      list($l[program], $l[msg]) = explode("||", $l[msg]);
    } else {

      $l[msg] = preg_replace("/^" . $l[program] . ":\ /", "", $l[msg]);

    }



    $x  = "INSERT INTO syslog (`host` , `facility` , `priority` , `level` , `tag` , `datetime` , `program` , `msg` )";
    $x .= " VALUES ( '$host', '$l[facility]', '$l[priority]', '$l[level]', '$l[tag]', '$l[datetime]', '$l[program]', '$l[msg]' );";

    mysql_query($x);

    $add++;

  } else { $discard++; }

  mysql_query("DELETE FROM logs WHERE seq = '$l[seq]'");

  $total++;

}

#echo("$total records processed: $add added to database, $discard discarded");
?>
