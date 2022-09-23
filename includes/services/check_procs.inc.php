<?php

$check_cmd = \LibreNMS\Config::get('nagios_plugins') . '/check_procs ' . $service['service_param'];
