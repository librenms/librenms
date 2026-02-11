<?php

$check_cmd = \App\Facades\LibrenmsConfig::get('nagios_plugins') . '/check_dovecot ' . $service['service_param'];
