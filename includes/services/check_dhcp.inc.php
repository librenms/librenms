<?php

$check_cmd = \App\Facades\Config::get('nagios_plugins') . '/check_dhcp ' . $service['service_param'];
