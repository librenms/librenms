<?php

$check = shell_exec($config['nagios_plugins'] . "/check_ntp -H ".$service['hostname']." ".$service['service_param']);

list($check, $time) = split("\|", $check);

if(strstr($check, "NTP OK")) {
  $status = '1';
} else {
  $status = '0';
}

?>
