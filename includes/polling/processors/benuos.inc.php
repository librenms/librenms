<?php

echo 'Benu CPU Usage';
$proc = snmp_get($device, 'bSysAvgCPUUtil5Min.0', '-OvQs', 'BENU-HOST-MIB');
