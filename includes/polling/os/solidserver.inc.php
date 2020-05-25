<?php

$oid_list = ['eipHwApplianceModel.0', 'eipHwApplianceSerial.0', 'eipSdsVersionNumber.0'];

$eip = snmp_get_multi($device, $oid_list, '-OUQs', 'EIP-MON-MIB');

$version = $eip[0]['eipSdsVersionNumber'];
$hardware = $eip[0]['eipHwApplianceModel'];
$serial = $eip[0]['eipHwApplianceSerial'];
