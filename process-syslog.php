#!/usr/bin/php
<?php

include("config.php");

if(!$config['enable_syslog']) {
  echo("Syslog support disabled.\n");
  exit();
}

include("includes/syslog.php");

mysql_query("DELETE FROM `syslog` WHERE `processed` = '0' AND `msg` LIKE '%last message repeated%'");
mysql_query("DELETE FROM `syslog` WHERE `processed` = '0' AND `msg` LIKE '%Connection from UDP: [%]:%'");
mysql_query("DELETE FROM `syslog` WHERE `processed` = '0' AND `msg` LIKE '%Traceback%'");
mysql_query("DELETE FROM `syslog` WHERE `processed` = '0' AND `msg` LIKE '%PM-3-INVALID_BRIDGE_PORT%'");
mysql_query("DELETE FROM `syslog` WHERE `processed` = '0' AND `msg` LIKE '%RHWatchdog%'");
mysql_query("DELETE FROM `syslog` WHERE `processed` = '0' AND `msg` LIKE '%Hardware Monitoring%'");

#mysql_query("DELETE FROM `syslog` WHERE `program` LIKE 'SNMP-3-AUTHFAIL'");
#mysql_query("DELETE FROM `syslog` WHERE `program` LIKE 'SW_MATM-4-MACFLAP_NOTIF'");

mysql_query("DELETE FROM `syslog` WHERE `priority` = 'debug'");


## Delete all the old old old syslogs (as per config.php variable)

mysql_query("DELETE FROM `syslog` WHERE `datetime` < DATE_SUB(NOW(), INTERVAL ".$config['syslog_age'].")");

$q = mysql_query("SELECT * FROM `syslog` where `processed` = '0'");
while($entry = mysql_fetch_array($q)){

   process_syslog($entry, 1);

}

?>
