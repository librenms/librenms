<?php

$check_cmd = \App\Facades\Config::get('nagios_plugins') . '/check_dovecot ' . $service['service_param'];
