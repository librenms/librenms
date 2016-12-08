<?php
$check_cmd = $config['nagios_plugins'] . "/check_ntp -H ".$service['hostname']." ".$service['service_param'];
