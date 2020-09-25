<?php

$check_cmd = 'sudo ' . \LibreNMS\Config::get('nagios_plugins') . '/check_mailqueue ' . $service['service_param'];
