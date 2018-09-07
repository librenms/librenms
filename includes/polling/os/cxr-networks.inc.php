<?php
$hardware = trim(snmp_get($device, '.1.3.6.1.4.1.1425.1040.1.1.0', '-OQv'), '"');
$version = trim(snmp_get($device, '.1.3.6.1.4.1.1425.1040.1.2.0', '-OQv'), '"');
