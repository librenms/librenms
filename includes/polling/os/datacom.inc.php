<?php

$hardware = 'Datacom ' . str_replace('dmSwitch', 'DM', snmp_get($device, 'swChassisModel.0', '-Ovq', 'DMswitch-MIB'));
$version = snmp_get($device, 'swFirmwareVer.1', '-Ovq', 'DMswitch-MIB');
$features = $device['sysDescr'];
$serial = snmp_get($device, 'DMswitch-MIB::swSerialNumber.1', '-Ovq', 'DMswitch-MIB');
