<?php

if ($service['service_port']) {
    $port = $service['service_port'];
} else {
    $port = '23';
}
$check_cmd = \App\Facades\LibrenmsConfig::get('nagios_plugins') . '/check_telnet -H ' . $service['hostname'] . ' -p ' . $port . ' ' . $service['service_param'];
