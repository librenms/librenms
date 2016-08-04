<?php

if (strpos($device["sysObjectID"], "enterprises.674.10895.3031") !== false) {
    echo "Dell Powerconnect 55xx";
    $proc = snmp_get($device, $processor['processor_oid'], '-O Uqnv', '""');
} elseif (strpos($device["sysObjectID"], "enterprises.674.10895.3024") !== false) {
    echo "Dell Powerconnect 8024F";
    $values = trim(snmp_get($device, $processor['processor_oid'], '-O Uqnv'), '""');
    $values = ltrim($values,' ');
    preg_match('/(\d*\.\d*)/', $values, $matches);
    $proc = $matches[0];
} else {
    $values = trim(snmp_get($device, 'dellLanExtension.6132.1.1.1.1.4.4.0', '-OvQ', 'Dell-Vendor-MIB'), '"');
    preg_match('/5 Sec \((.*)%\),.*1 Min \((.*)%\),.*5 Min \((.*)%\)$/', $values, $matches);
    $proc = $matches[3];
}
