<?php

// For use with Graylog2 plugin by Catinello found at https://marketplace.graylog.org/addons/9ee98819-804e-41c3-b0ac-6ca7975c1a48
// example parameters: -l https://graylog1.example.com:9000/api -insecure -p password -u username -w 10 -c 20
$check_cmd = \LibreNMS\Config::get('nagios_plugins') . '/check_graylog ' . $service['service_param'];
