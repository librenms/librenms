<?php

$check_cmd = 'sudo ' . \LibreNMS\Config::get('nagios_plugins') . '/check_postfix ' . $service['service_param'];
