<?php

$check_cmd = 'sudo ' . \App\Facades\Config::get('nagios_plugins') . '/check_mailqueue ' . $service['service_param'];
