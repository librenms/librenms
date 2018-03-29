<?php

$version = snmp_get($device, 'sysConfFirmwareVersion.0', '-Ovq', 'AIRPORT-BASESTATION-3-MIB');
