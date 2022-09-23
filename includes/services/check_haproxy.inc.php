<?php

$check_cmd = \LibreNMS\Config::get('nagios_plugins') . '/check_haproxy ' . $service['service_param'];
