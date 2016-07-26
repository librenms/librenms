<?php

// Code below was borrowed from 'powerconnect-cpu.inc.php'


//--------------------------------------------------------------------//
// Dell-Vendor-MIB::dellLanExtension.6132.1.1.1.1.4.1.0 = INTEGER: 23127
// Dell-Vendor-MIB::dellLanExtension.6132.1.1.1.1.4.2.0 = INTEGER: 262144
// Simple hard-coded poller for Dell Powerconnect (tested on 6248P)
// Yes, it really can be this simple.
// Pity there's no matching MIB to be found.

$sysObjectId = snmp_get($device, 'SNMPv2-MIB::sysObjectID.0', '-Ovqn');

if (strstr($sysObjectId, '.1.3.6.1.4.1.6027.1.3.')) {
        echo 'S-Series ';
        $index = $mempool['mempool_index'];
        $mempool['total'] = snmp_get($device, ".1.3.6.1.4.1.6027.3.10.1.2.7.1.4.1." . $mempool['mempool_index'], "-OvQ");
        $mempool['total'] *= 1048576; // FTOS display memory in MB
        $mempool['perc'] = snmp_get($device, ".1.3.6.1.4.1.6027.3.10.1.2.9.1.5.1." . $mempool['mempool_index'], "-OvQ");
        $mempool['used'] = $mempool['total'] * $mempool['perc'] / 100;
        $mempool['free'] = $mempool['total'] - $mempool['used'];
} elseif (strstr($sysObjectId, '.1.3.6.1.4.1.6027.1.2.')) {
        echo 'C-Series ';
        $index = $mempool['mempool_index'];
        $mempool['total'] = 1090519040; // FTOS display memory in MB
        $mempool['perc'] = snmp_get($device, ".1.3.6.1.4.1.6027.3.8.1.3.7.1.6.1." . $mempool['mempool_index'], "-OvQ");
        $mempool['used'] = $mempool['total'] * $mempool['perc'] / 100;
        $mempool['free'] = $mempool['total'] - $mempool['used'];
} elseif (strstr($sysObjectId, '.1.3.6.1.4.1.6027.1.1.')) {
        echo 'E-Series ';
        $index = $mempool['mempool_index'];
        $mempool['total'] = snmp_get($device, "1.3.6.1.4.1.6027.3.1.1.2.6.1.6.1." . $mempool['mempool_index'], "-OvQ");
        $mempool['total'] *= 1048576; // FTOS display memory in MB
        $mempool['perc'] = snmp_get($device, ".1.3.6.1.4.1.6027.3.1.1.3.7.1.6.1." . $mempool['mempool_index'], "-OvQ");
        $mempool['used'] = $mempool['total'] * $mempool['perc'] / 100;
        $mempool['free'] = $mempool['total'] - $mempool['used'];
} else {
        $mempool['total'] = snmp_get($device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.2.0', '-OvQ');
        $mempool['free']  = snmp_get($device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.1.0', '-OvQ');
        $mempool['used']  = ($mempool['total'] - $mempool['free']);
}
