<?php

echo 'atPluggableDiagTempEntry ';
$pre_cache['awplus-sfpddm'] = snmpwalk_cache_oid($device, 'atPluggableDiagTempEntry', [], 'AT-PLUGGABLE-DIAGNOSTICS-MIB');

echo 'atPluggableDiagVccEntry ';
$pre_cache['awplus-sfpddm'] = snmpwalk_cache_oid($device, 'atPluggableDiagVccEntry', $pre_cache['awplus-sfpddm'], 'AT-PLUGGABLE-DIAGNOSTICS-MIB');

echo  'atPluggableDiagTxBiasEntry ';
$pre_cache['awplus-sfpddm'] = snmpwalk_cache_oid($device, 'atPluggableDiagTxBiasEntry', $pre_cache['awplus-sfpddm'], 'AT-PLUGGABLE-DIAGNOSTICS-MIB');

echo 'atPluggableDiagTxPowerEntry ';
$pre_cache['awplus-sfpddm'] = snmpwalk_cache_oid($device, 'atPluggableDiagTxPowerEntry', $pre_cache['awplus-sfpddm'], 'AT-PLUGGABLE-DIAGNOSTICS-MIB');

echo 'atPluggableDiagRxPowerEntry ';
$pre_cache['awplus-sfpddm'] = snmpwalk_cache_oid($device, 'atPluggableDiagRxPowerEntry', $pre_cache['awplus-sfpddm'], 'AT-PLUGGABLE-DIAGNOSTICS-MIB');

echo 'atPluggableDiagRxLosTable ';
$pre_cache['awplus-sfpddm'] = snmpwalk_cache_oid($device, 'atPluggableDiagRxLosTable', $pre_cache['awplus-sfpddm'], 'AT-PLUGGABLE-DIAGNOSTICS-MIB');
