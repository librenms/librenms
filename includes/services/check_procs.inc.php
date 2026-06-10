<?php

$check_cmd = \App\Facades\LibrenmsConfig::get('nagios_plugins') . '/check_procs ' . $service['service_param'];
