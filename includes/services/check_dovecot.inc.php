<?php

$check_cmd = \LibreNMS\Config::get('nagios_plugins') . '/check_dovecot ' . $service['service_param'];
