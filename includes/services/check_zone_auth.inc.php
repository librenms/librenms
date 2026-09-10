<?php

$check_cmd = \App\Facades\LibrenmsConfig::get('nagios_plugins') . '/check_zone_auth ' . $service['service_param'];
