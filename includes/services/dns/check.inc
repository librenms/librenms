<?php

// provide some sane default
if ($service['service_param']) { $nsquery  = $service['service_param']; } else { $nsquery  = "localhost";          }
if ($service['service_ip'])    { $resolver = $service['service_ip'];    } else { $resolver = $service['hostname']; }

$check = shell_exec($config['nagios_plugins'] . "/check_dns -H ".$nsquery." -s ".$resolver);

list($check, $time) = split("\|", $check);

if(strstr($check, "DNS OK: ")) {
  $status = '1';
} else {
  $status = '0';
}

?>
