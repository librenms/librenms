<?php

$version = trim(snmp_get($device, '1.3.6.1.2.1.33.1.1.3.0', '-OQv', 'UPS-MIB'), '"');
