<?php

$check_cmd = \App\Facades\Config::get('nagios_plugins') . '/check_inodes ' . $service['service_param'];
