<?php

$check = shell_exec($config['nagios_plugins'] . "/check_imap -H ".$service['hostname']);

list($check, $time) = split("\|", $check);

if(strstr($check, "IMAP OK - ")) {
  $status = '1';
} else {
  $status = '0';
}

?>
