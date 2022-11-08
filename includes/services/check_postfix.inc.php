<?php

$check_cmd = 'sudo ' . \App\Facades\Config::get('nagios_plugins') . '/check_postfix ' . $service['service_param'];
