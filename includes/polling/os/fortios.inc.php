<?php

$serial = snmp_get($device, 'fnSysSerial', '-OQv', 'FORTINET-FORTIANALYZER-MIB');
