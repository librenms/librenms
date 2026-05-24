<?php

if ($service['service_ip']) {
    $server = $service['service_ip'];
} else {
    $server = $service['hostname'];
}

$check_cmd = App\Facades\LibrenmsConfig::get('nagios_plugins') . '/check_dhcp -s ' . $server . ' ' . $service['service_param'];
