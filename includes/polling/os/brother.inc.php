<?php

// SNMPv2-SMI::enterprises.2435.2.4.3.2435.5.13.3.0 = STRING: "Brother HL-2070N series"
$hardware = trim(snmp_get($device, '1.3.6.1.4.1.2435.2.4.3.2435.5.13.3.0', '-OQv', '', ''), '" ');

// SNMPv2-SMI::enterprises.11.2.3.9.4.2.1.1.3.3.0 = STRING: "A7J913764"
$serial = trim(snmp_get($device, '1.3.6.1.4.1.11.2.3.9.4.2.1.1.3.3.0', '-OQv', '', ''), '" ');

// SNMPv2-SMI::enterprises.2435.2.4.3.1240.6.5.0 = STRING: "Firmware Ver.1.33 (06.07.21)"
$version = trim(snmp_get($device, '1.3.6.1.4.1.2435.2.4.3.1240.6.5.0', '-OQv', '', ''), '" ');

preg_match('/Ver\.(.*) \(/', $version, $matches);
if ($matches[1]) {
    $version = $matches[1];
}

// SNMPv2-SMI::enterprises.2435.2.3.9.1.1.7.0 = STRING: "MFG:Brother;CMD:HBP,PJL,PCL,PCLXL,POSTSCRIPT;MDL:MFC-8440;CLS:PRINTER;"
if ($hardware == '') {
    $jdinfo = explode(';', trim(snmp_get($device, '1.3.6.1.4.1.2435.2.3.9.1.1.7.0', '-OQv', '', ''), '" '));

    foreach ($jdinfo as $jdi) {
        list($key,$value) = explode(':', $jdi);
        $jetdirect[$key]  = $value;
    }

    $hardware = $jetdirect['MDL'];
}

// SNMPv2-SMI::enterprises.2435.2.3.9.4.2.1.5.5.1.0 = STRING: "000A5J431816"
if ($serial == '') {
    $serial = trim(snmp_get($device, '1.3.6.1.4.1.2435.2.3.9.4.2.1.5.5.1.0', '-OQv', '', ''), '" ');
}

// Strip off useless brand fields
$hardware = str_replace('Brother ', '', $hardware);
$hardware = str_ireplace(' series', '', $hardware);

if (isHexString($serial)) {
    // Sometimes firmware outputs serial as hex-string
    $serial = snmp_hexstring($serial);
}
