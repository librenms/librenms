<?php

$data = snmp_get_multi($device, 'upsIdentModel.0 upsIdentUPSSoftwareVersion.0 upsIdentName.0', '-OQU', 'UPS-MIB');
$hardware = $data[0]['UPS-MIB::upsIdentModel'];
$version = $data[0]['UPS-MIB::upsIdentUPSSoftwareVersion'];
