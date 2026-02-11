<?php

$check_cmd = \App\Facades\LibrenmsConfig::get('nagios_plugins') . '/check_hetzner_storagebox ' . $service['service_param'];
