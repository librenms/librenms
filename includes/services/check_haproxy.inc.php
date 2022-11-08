<?php

$check_cmd = \App\Facades\Config::get('nagios_plugins') . '/check_haproxy ' . $service['service_param'];
