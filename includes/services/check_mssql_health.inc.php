<?php

$check_cmd = \App\Facades\LibrenmsConfig::get('nagios_plugins') . '/check_mssql_health --server ';

if ($service['service_ip']) {
    $check_cmd .= $service['service_ip'];
} else {
    $check_cmd .= $service['server'];
}
$check_cmd .= ' ' . $service['service_param'];
