<?php

$hardware = snmp_get($device, 'sysObjectID.0', '-Oqvs', 'FOUNDRY-SN-AGENT-MIB:FOUNDRY-SN-ROOT-MIB');

$hardware = rewrite_ironware_hardware($hardware);

$version = snmp_get($device, 'snAgBuildVer.0', '-Oqvs', 'FOUNDRY-SN-AGENT-MIB:FOUNDRY-SN-ROOT-MIB');

if (strpos($version, '.')) {
    $version = str_replace('V', '', $version);
    $version = str_replace('"', '', $version);
} else {
    // Brocade NetIron CER, Extended route scalability, IronWare Version V5.6.0fT183 Compiled on Mar 27 2015 at 02:13:25 labeled as V5.6.00fb
    // Brocade MLXe (System Mode: XMR), IronWare Version V5.6.0gT163 Compiled on Aug 27 2015 at 23:23:54 labeled as V5.6.00g
    preg_match('/IronWare Version (.*) Compiled on/', $device['sysDescr'], $regexp_result);
    $version = $regexp_result[1];
    $version = str_replace('V', '', $version);
}

$serial = snmp_get($device, 'snChasSerNum.0', '-Ovq', 'FOUNDRY-SN-AGENT-MIB');
