<?php

#Get check_domain from https://raw.githubusercontent.com/glensc/nagios-plugin-check_domain/master/check_domain.sh
$check_cmd = $config['nagios_plugins'] . "/check_domain -d ";
if ($service['service_ip']) {
    $check_cmd .= $service['service_ip'];
} else {
    $check_cmd .= $service['hostname'];
}
$check_cmd .= " ".$service['service_param'];
