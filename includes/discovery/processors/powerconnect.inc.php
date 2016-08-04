<?php

if ($device['os'] == 'powerconnect') {
    $trimmed_sysObjectId = ltrim($device["sysObjectID"],"enterprise.");
    if ($trimmed_sysObjectId == "674.10895.3031") {
        echo "Dell Powerconnect 55xx";
        $usage = trim(snmp_get($device, '.1.3.6.1.4.1.89.1.7.0', '-Ovq'));
        discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.89.1.7.0', '0', 'powerconnect', 'Processor', '1', $usage, null, null);
    } elseif ($trimmed_sysObjectId == "674.10895.3024") {
        echo "Dell Powerconnect 8024F";
        $usage = trim(snmp_get($device,'.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.9.0', '-Ovq'), '"');
        $usage = ltrim($usage,' ');
        if (substr($usage, 0, 5) == '5 Sec') {
            discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.9.0', '0', 'powerconnect', 'Processor', '1', $usage, null, null);
    } else {
        $descr = 'Processor';
        $usage = trim(snmp_get($device, 'dellLanExtension.6132.1.1.1.1.4.4.0', '-OQUvs', 'Dell-Vendor-MIB'), '"');
        if (substr($usage, 0, 5) == '5 Sec') {
            discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.4.0', '0', 'powerconnect', $descr, '1', $usage, null, null);
        }
    }
}
