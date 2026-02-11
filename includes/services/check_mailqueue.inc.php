<?php

$check_cmd = 'sudo ' . \App\Facades\LibrenmsConfig::get('nagios_plugins') . '/check_mailqueue ' . $service['service_param'];
