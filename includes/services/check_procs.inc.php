<?php

$check_cmd = \App\Facades\Config::get('nagios_plugins') . '/check_procs ' . $service['service_param'];
