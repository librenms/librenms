<?php
$check_cmd = $config['nagios_plugins'] . "/check_pop -H ".$service['hostname']." ".$service['service_param'];
