<?php

// provide some sane default
if ($service['service_ip'])    { $dhcp = $service['service_ip'];    } else { $dhcp = $service['hostname']; }

$check_cmd = $config['nagios_plugins'] . "/check_dhcp -s ".$dhcp;
