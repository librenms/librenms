<?php

$check = shell_exec($config['nagios_plugins'] . "/check_ftp -H ".$service['hostname']);

list($check, $time) = split("\|", $check);

if(strstr($check, "FTP OK - ")) {
  $status = '1';
} else {
  $status = '0';
}

?>
