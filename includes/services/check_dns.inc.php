<?php

// provide some sane default
if ($service['service_param']) {
    $nsquery = $service['service_param'];
} else {
    $nsquery = 'localhost';
}
if ($service['service_ip']) {
    $resolver = $service['service_ip'];
} elseif ($service['overwrite_ip']) {
    $resolver = $service['overwrite_ip'];
} else {
    $resolver = $service['hostname'];
}

$check_cmd = \LibreNMS\Config::get('nagios_plugins') . '/check_dns -H ' . $nsquery . ' -s ' . $resolver;
