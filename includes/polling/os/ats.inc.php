<?php

$hardware = snmp_get($device, 'atsIdentGroupFirmwareRevision.0', '-OQv', 'ATS-MIB');
