<?php
$check_cmd = $config['nagios_plugins'] . "/check_ssh -H ".$service['hostname']." ".$service['service_param'];
