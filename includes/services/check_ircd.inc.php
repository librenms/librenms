<?php

$check_cmd = $config['nagios_plugins'] . "/check_ircd -H ".$service['hostname']." ".$service['service_param'];
