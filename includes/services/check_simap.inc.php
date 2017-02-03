<?php
$check_cmd = $config['nagios_plugins'] . "/check_simap -H ".$service['hostname']." ".$service['service_param'];
