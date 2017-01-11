<?php
$check_cmd = $config['nagios_plugins'] . "/check_smtp -H ".$service['hostname']." ".$service['service_param'];
