<?php

$oid_list = ['mwSystemGeneralVersion.0', 'mwSystemGeneralModel.0'];
$fortiwlc = snmp_get_multi_oid($device, $oid_list, '-OUQs', 'MERU-GLOBAL-STATISTICS-MIB');
$version = $fortiwlc[0]['mwSystemGeneralVersion.0'];
$hardware = $fortiwlc[0]['mwSystemGeneralModel.0'];
