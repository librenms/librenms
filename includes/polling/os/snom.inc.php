<?php

$version_info = snmp_get($device, '1.3.6.1.2.1.7526.2.4', '-Oqv');
list($hardware, $features, $version) = explode(' ', str_replace('-', ' ', $version_info), 3);
