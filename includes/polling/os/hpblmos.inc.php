<?php
$version = trim(snmp_get($device, 'SNMPv2-SMI::enterprises.232.22.2.3.1.1.1.8.1', '-Ovq'), '"');
$hardware = trim(snmp_get($device, 'SNMPv2-SMI::enterprises.232.22.2.3.1.1.1.3.1', '-Ovq'), '"');
