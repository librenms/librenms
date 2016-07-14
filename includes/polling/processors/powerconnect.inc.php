<?php
$sysObjectId = snmp_get($device, 'SNMPv2-MIB::sysObjectID.0', '-Ovqn');
switch ($sysObjectId) {
case '.1.3.6.1.4.1.674.10895.3031':
    /*
     * Devices supported:
     * Dell Powerconnect 55xx
     */
    $proc = snmp_get($device, $processor['processor_oid'], '-O Uqnv', '""');
    break;

case '.1.3.6.1.4.1.674.10895.3024':
     /*
      * Devices supported:
      * Dell Powerconnect 8024F
      */
      $values = trim(snmp_get($device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.9.0', '-OvQ'), '"');
      $values = ltrim($values,' ');
      preg_match('/(\d*\.\d*)/', $values, $matches);
      $proc = $matches[0];
      break;

default:
    $values = trim(snmp_get($device, 'dellLanExtension.6132.1.1.1.1.4.4.0', '-OvQ', 'Dell-Vendor-MIB'), '"');
    preg_match('/5 Sec \((.*)%\),.*1 Min \((.*)%\),.*5 Min \((.*)%\)$/', $values, $matches);
    $proc = $matches[3];
}
