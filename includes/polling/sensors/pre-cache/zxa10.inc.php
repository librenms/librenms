<?php
if ($type == 'dbm')
{
    d_echo('Pre-caching zxAnPonRxOpticalPower');
    $sensor_cache['zxa10_onu'] = snmpwalk_cache_oid($device, 'zxAnPonRxOpticalPower', [], 'ZTE-AN-PON-BASE-MIB');
    d_echo('Pre-caching zxAnOpticalIfRxPwrCurrValue');
    $sensor_cache['zxa10_olt'] = snmpwalk_cache_oid($device, 'zxAnOpticalIfRxPwrCurrValue', [], 'ZTE-AN-OPTICAL-MODULE-MIB');
}