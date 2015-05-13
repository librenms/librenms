<?php
$check = shell_exec($config['nagios_plugins'] . "/check_icmp ".($service['service_ip'] ? $service['service_ip'] : $service['hostname']));

list($check, $time) = split("\|", $check);

if(strstr($check, "OK - ")) {
  $status = '1';
} else {
  $status = '0';
}

?>
