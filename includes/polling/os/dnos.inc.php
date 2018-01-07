<?php

/*
sysDescr = "Dell Networking OS
Operating System Version: 2.0
Application Software Version: 9.7(0.0P4)
Series: S4810
Copyright (c) 1999-2015 by Dell Inc. All Rights Reserved.
Build Time: Mon May 4 20:52:56 2015";
*/

$temp_hardware = snmp_get($device, 'productIdentificationDisplayName.0', '-Ovq', 'Dell-Vendor-MIB');

if (preg_match('/Dell Networking N[1234].*/', $temp_hardware) == 1) {  // If Dell N-Series
    $hardware = $temp_hardware;
    $version  = snmp_get($device, 'productIdentificationVersion.0', '-Ovq', 'Dell-Vendor-MIB');
    $features = snmp_get($device, 'productIdentificationDescription.0', '-Ovq', 'Dell-Vendor-MIB');
} else {  // Assume S-series
    $sysDescr =  preg_replace('/[\r]+/', ' ', $device['sysDescr']);
    list(,,$version,$hardware,,) = explode(PHP_EOL, $sysDescr);
    list(,$version) = explode(': ', $version);
    list(,$hardware) = explode(': ', $hardware);
}
