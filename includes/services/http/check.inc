<?php

$check = shell_exec($config['nagios_plugins'] . "/check_http -H ".$service['hostname']." ".$service['service_param']);

list($check, $time) = split("\|", $check);

if(strstr($check, "HTTP OK")) {
  $status = '1';
} else {
  $status = '0';
}

?>
