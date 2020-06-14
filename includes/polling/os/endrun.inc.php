<?php

// EndRun Technologies Polling OIDs for Tempus LX NTP devices
$endrun_hardware = snmp_get_multi($device, ['cntpVersion.0'], '-OQUs', 'TEMPUSLXUNISON-MIB');

#$hardware = $gigamon_hardware[0]['model'];
$version = $endrun_hardware[0]['cntpVersion'];
#$serial = $gigamon_hardware[0]['serialNumber'];
