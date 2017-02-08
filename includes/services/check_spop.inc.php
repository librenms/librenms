<?php
$check_cmd = $config['nagios_plugins'] . "/check_spop -H ".$service['hostname']." ".$service['service_param'];
