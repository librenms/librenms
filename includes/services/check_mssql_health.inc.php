<?php

$check_cmd = \LibreNMS\Config::get('nagios_plugins') . '/check_mssql_health --server ';

if ($service['service_ip']) {
    $check_cmd .= $service['service_ip'];
} else {
    $check_cmd .= $service['server'];
}
$check_cmd .= ' ' . $service['service_param'];
