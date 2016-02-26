<?php

$check = shell_exec($config['nagios_plugins'] . "/check_simap -H ".$service['hostname']);

list($check, $time) = explode("|", $check);

if(strstr($check, "SIMAP OK")) {
  $status = '1';
} else {
  $status = '0';
}

?>
