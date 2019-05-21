<?php


$alcoma_tmp = snmp_get_multi_oid($device, ['alMPModel.0', 'alMPVersionSW.0', 'alMPSerNumMW.0'], '-OQs', 'ALCOMA-MIB');
$hardware      = $alcoma_tmp['alMPModel.0'];
$version       = $alcoma_tmp['alMPVersionSW.0'];
$serial        = $alcoma_tmp['alMPSerNumMW.0'];
unset($alcoma_tmp);
