<?php

// provide some sane default
if ($service['service_param']) { $dbname = $service['service_param']; } else { $dbname = "mysql"; }

$check = shell_exec($config['nagios_plugins'] . "/check_mysql -H ".$service['hostname']." ".$dbname);

list($check, $time) = split("\|", $check);

if(strstr($check, "Uptime:")) {
  $status = '1';
} else {
  $status = '0';
}

?>
