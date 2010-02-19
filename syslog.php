#!/usr/bin/php
<?php

#  ini_set('display_errors', 0);
#  ini_set('display_startup_errors', 0);
#  ini_set('log_errors', 0);
#  ini_set('error_reporting', E_ALL);


include("config.php");
include("includes/syslog.php");

$i="1";

#mysql_query("DELETE FROM `syslog` WHERE `datetime` < DATE_SUB(NOW(), INTERVAL ".$config['syslog_age'].")");

$s=fopen('php://stdin','r');
while($line=fgets($s)){
 `echo "$line" >> /tmp/syslog`;
 list($entry['host'],$entry['facility'],$entry['priority'], $entry['level'], $entry['tag'], $entry['timestamp'], $entry['msg']) = explode("||", trim($line));
 shell_exec('echo "'.$i.'. '.$entry['host'].' -> '.$entry['msg'].'" >> /tmp/syslog');

 process_syslog($entry, 1);

 unset($entry); unset($line);
 $i++;
}

?>
