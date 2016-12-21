<?php

$check_cmd = $config['nagios_plugins'] . "/check_imap -H ".$service['hostname']." ".$service['service_param'];
