<?php

$check_cmd = 'sudo ' . \App\Facades\LibrenmsConfig::get('nagios_plugins') . '/check_postfix ' . $service['service_param'];
