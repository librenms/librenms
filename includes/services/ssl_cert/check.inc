<?php

$cmd = $config['nagios_plugins'] . "/check_ssl_cert -H ";
if( !empty($service['service_ip']) ) {
	$cmd .= $service['service_ip'];
} else {
	$cmd .= $service['hostname'];
}
$cmd .= " ".$service['service_param'];

$check = shell_exec($cmd);
list($check, $time) = explode("\|", $check);

if(strstr($check, "SSL_CERT OK")) {
  $status = '1';
} else {
  $status = '0';
}

?>
