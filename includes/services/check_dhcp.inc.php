<?php

$check_cmd = \App\Facades\LibrenmsConfig::get('nagios_plugins') . '/check_dhcp ' . $service['service_param'];
