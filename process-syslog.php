#!/usr/bin/php
<?php

include("config.php");
include("includes/functions.php");

mysql_query("DELETE FROM `syslog` WHERE `msg` LIKE '%last message repeated%'");
mysql_query("DELETE FROM `syslog` WHERE `msg` LIKE '%Connection from UDP: [89.21.224.44]:%'");
mysql_query("DELETE FROM `syslog` WHERE `msg` LIKE '%Connection from UDP: [89.21.224.35]:%'");


if(!$config['enable_syslog']) { 
  echo("Syslog support disabled.\n"); 
  exit(); 
}

$q = mysql_query("SELECT * FROM `syslog` where `processed` = '0'");
while($entry = mysql_fetch_array($q)){

   process_syslog($entry, 1);

}

?>
