<?php

$check_cmd = \App\Facades\LibrenmsConfig::get('nagios_plugins') . '/check_haproxy ' . $service['service_param'];
