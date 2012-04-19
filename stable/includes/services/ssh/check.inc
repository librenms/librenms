<?php

$check = shell_exec($config['nagios_plugins'] . "/check_ssh -H ".$service['hostname']);

list($check, $time) = split("\|", $check);

if(strstr($check, "SSH OK")) {
  $status = '1';
} else {
  $status = '0';
}

?>
