<?php

$check_cmd = "sudo " . $config['nagios_plugins'] . "/check_mailqueue ".$service['service_param'];
