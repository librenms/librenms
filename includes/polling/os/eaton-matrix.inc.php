<?php



$eaton_matrix_data = snmp_get_multi_oid($device, 'matConName.0 matAgentSoftwareVerison.0 matConSerialNum.0', '-OQUs', 'TELECOM-MIB');

$hardware = trim($eaton_matrix_data['matConName.0'], '"');
$version  = trim($eaton_matrix_data['matAgentSoftwareVersion.0'], '"');
$serial   = trim($eaton_matrix_data['matConSerialNum.0'], '"');

unset($eaton_matrix_data);
