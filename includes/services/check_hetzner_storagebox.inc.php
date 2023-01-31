<?php

$check_cmd = \LibreNMS\Config::get('nagios_plugins') . '/check_hetzner_storagebox ' . $service['service_param'];
