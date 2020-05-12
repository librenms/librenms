<?php

//SNMPv2-SMI::enterprises.6527.3.1.2.1.1.5.0 = Gauge32: 9 - sgiSwMajorVersion
//SNMPv2-SMI::enterprises.6527.3.1.2.1.1.6.0 = Gauge32: 0 - sgiSwMinorVersion
//SNMPv2-SMI::enterprises.6527.3.1.2.1.1.7.0 = STRING: "R3" - sgiSwVersionModifier

$majorVersion = trim(snmp_get($device, '1.3.6.1.4.1.6527.3.1.2.1.1.5.0', '-OQv', '', ''), '" ');
$minorVersion = trim(snmp_get($device, '1.3.6.1.4.1.6527.3.1.2.1.1.6.0', '-OQv', '', ''), '" ');
$versionModifier = trim(snmp_get($device, '1.3.6.1.4.1.6527.3.1.2.1.1.7.0', '-OQv', '', ''), '" ');

$version = 'v' . $majorVersion . '.' . $minorVersion . '.' . $versionModifier;

$hardwareindex = snmp_get($device, 'tmnxChassisType.1', '-Ovq', 'TIMETRA-CHASSIS-MIB');
$tmnxChassisTypeName = 'tmnxChassisTypeName' . '.' . $hardwareindex;
$hardware = snmp_get($device, $tmnxChassisTypeName, '-Ovq', 'TIMETRA-CHASSIS-MIB');

$props = snmpwalk_cache_numerical_oid($device, 'tmnxHwEntry.7', $props = array(), 'TIMETRA-CHASSIS-MIB', null, '-OQne');
foreach ($props as $p) {
    foreach ($p as $k => $v) {
        if ($v == 3) {
            $shrapnel = explode('.', $k);
            $unitID =  end($shrapnel);
            $serial = snmp_get($device, "1.3.6.1.4.1.6527.3.1.2.2.1.8.1.5.1.$unitID", '-OQv', 'TIMETRA-CHASSIS-MIB');
            unset($shrapnel);
        }
    }
}
unset($props, $p, $k, $v, $unitID);
