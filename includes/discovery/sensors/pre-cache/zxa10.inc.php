<?php
echo 'Caching ZTE-AN-OPTICAL-MODULE-MIB and ZTE-AN-PON-BASE-MIB';
$pre_cache['zxa10_oids'] = snmpwalk_cache_multi_oid($device, 'zxAnOpticalIfRxPwrCurrValue', [], 'ZTE-AN-OPTICAL-MODULE-MIB');
$pre_cache['zxa10_onu_oids'] = snmpwalk_cache_multi_oid($device, 'zxAnPonRxOpticalPower', [], 'ZTE-AN-PON-BASE-MIB');