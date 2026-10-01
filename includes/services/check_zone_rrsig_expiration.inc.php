<?php

$check_cmd = \App\Facades\LibrenmsConfig::get('nagios_plugins') . '/check_zone_rrsig_expiration ' . $service['service_param'];
