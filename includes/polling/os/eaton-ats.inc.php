<?php

$data = snmp_get_multi($device, 'ats2IdentPartNumber.0 ats2IdentFWVersion.0 ats2IdentSerialNumber.0', '-OQU', 'EATON-ATS2-MIB');
$hardware = $data[0]['EATON-ATS2-MIB::ats2IdentPartNumber'];
$version = $data[0]['EATON-ATS2-MIB::ats2IdentFWVersion'];
$serial = $data[0]['EATON-ATS2-MIB::ats2IdentSerialNumber'];
