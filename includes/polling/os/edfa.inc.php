<?php
$edfa_tmp = snmp_get_multi_oid($device, ['commonDeviceModelNumber.1', 'commonDeviceSerialNumber.1'], '-OUQs', 'NSCRTV-ROOT');
$hardware = $edfa_tmp['commonDeviceModelNumber.1'];
$serial   = $edfa_tmp['commonDeviceSerialNumber.1'];
