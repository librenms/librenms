#!/usr/bin/php
<?php

include("config.php");
include("includes/syslog.php");

$i="1";

$s=fopen('php://stdin','r');
while($line=fgets($s)){
 list($entry['host'],$entry['facility'],$entry['priority'], $entry['level'], $entry['tag'], $entry['timestamp'], $entry['msg']) = explode("||", trim($line));
 process_syslog($entry, 1);
 unset($entry); unset($line);
 $i++;
}

?>
