<?php

$check_cmd = \LibreNMS\Config::get('nagios_plugins') . '/check_inodes ' . $service['service_param'];
