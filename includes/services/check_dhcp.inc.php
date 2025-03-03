<?php

$check_cmd = \LibreNMS\Config::get('nagios_plugins') . '/check_dhcp ' . $service['service_param'];
