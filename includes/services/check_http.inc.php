<?php
$check_cmd = $config['nagios_plugins'] . "/check_http -I " . ($service['service_ip'] ? $service['service_ip'] : $service['hostname']) . " " .$service['service_param'];
