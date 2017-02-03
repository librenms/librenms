<?php

$check_cmd = $config['nagios_plugins'] . "/check_ftp -H ".$service['hostname']." ".$service['service_param'];
