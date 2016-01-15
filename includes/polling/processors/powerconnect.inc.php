<?php
$sysObjectId = $poll_device['sysObjectID'];
switch ($sysObjectId) {
case '.1.3.6.1.4.1.674.10895.3031':
    /*
     * Devices supported:
     * Dell Powerconnect 55xx
     */
    $proc = snmp_get($device, $processor['processor_oid'], '-O Uqnv', '""');
    break;

default:
    $values = trim(snmp_get($device, 'dellLanExtension.6132.1.1.1.1.4.4.0', '-OvQ', 'Dell-Vendor-MIB'), '"');
    preg_match('/5 Sec \((.*)%\),.*1 Min \((.*)%\),.*5 Min \((.*)%\)$/', $values, $matches);
    $proc = $matches[3];
}
