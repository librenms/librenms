<?php

#Get check_domain from https://raw.githubusercontent.com/glensc/nagios-plugin-check_domain/master/check_domain.sh
$cmd = $config['nagios_plugins'] . "/check_domain -d ";
if( $service['service_ip'] ) {
	$cmd .= $service['service_ip'];
} else {
	$cmd .= $service['hostname'];
}
$cmd .= " ".$service['service_param'];

$check = shell_exec($cmd);
list($check, $time) = split("\|", $check);

if(strstr($check, "OK - Domain")) {
  $status = '1';
} else {
  $status = '0';
}

?>
