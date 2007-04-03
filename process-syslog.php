#!/usr/bin/php
<?php

include("config.php");
include("includes/functions.php");

$add = 0;
$discard = 0;
$total = 0;


$q = mysql_query("SELECT * FROM `logs`");
while($l = mysql_fetch_array($q)){

  unset($host);
  unset($maybehost);
  unset($perhapshost);

  $maybehost = @mysql_result(mysql_query("SELECT D.id as id FROM ipaddr AS A, interfaces AS I, devices AS D WHERE A.addr = '$l[host]' AND I.id = A.interface_id AND D.id = I.host"),0);
  $perhapshost = @mysql_result(mysql_query("SELECT id FROM devices WHERE `hostname` = '$l[host]'"),0);

  if($maybehost) { 
    $host = $maybehost;
  } elseif($perhapshost) {
    $host = $perhapshost;
  }

  if($host) {

    if(mysql_result(mysql_query("SELECT os FROM `devices` WHERE `id` = '$host'"),0) == "IOS") {
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

echo("$total records processed: $add added to database, $discard discarded");

?>
