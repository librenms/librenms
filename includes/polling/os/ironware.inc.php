<?php

$hardware = snmp_get($device, 'sysObjectID.0', '-Oqvs', 'FOUNDRY-SN-AGENT-MIB:FOUNDRY-SN-ROOT-MIB');

$hardware = rewrite_ironware_hardware($hardware);

$version = snmp_get($device, 'snAgBuildVer.0', '-Oqvs', 'FOUNDRY-SN-AGENT-MIB:FOUNDRY-SN-ROOT-MIB');

$version = str_replace('V', '', $version);
$version = str_replace('"', '', $version);

$serial = snmp_get($device, 'snChasSerNum.0', '-Ovq', 'FOUNDRY-SN-AGENT-MIB');
