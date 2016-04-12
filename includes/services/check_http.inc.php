<?php

$check_cmd = $config['nagios_plugins'] . "/check_http -H ".$service['hostname']." ".$service['service_param'];
