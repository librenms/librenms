<?php

$check_cmd = \App\Facades\LibrenmsConfig::get('nagios_plugins') . '/check_inodes ' . $service['service_param'];
