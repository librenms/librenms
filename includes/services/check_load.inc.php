<?php

// provide some sane default
if ($service['service_param']) {
    $params  = $service['service_param'];
} else {
    $params  = "-w 5,5,5 -c 10,10,10";
}

$check_cmd = $config['nagios_plugins'] . "/check_load " . $params;
