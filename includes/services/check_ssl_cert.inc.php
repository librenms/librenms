<?php
$check_cmd = $config['nagios_plugins'] . "/check_ssl_cert -H ";
if (!empty($service['service_ip'])) {
    $check_cmd .= $service['service_ip'];
} else {
    $check_cmd .= $service['hostname'];
}
$check_cmd .= " ".$service['service_param'];
