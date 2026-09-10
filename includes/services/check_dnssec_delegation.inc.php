<?php

$check_cmd = \App\Facades\LibrenmsConfig::get('nagios_plugins') . '/check_dnssec_delegation ' . $service['service_param'];
