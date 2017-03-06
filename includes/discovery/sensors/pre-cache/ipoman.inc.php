<?php

d_echo('outletConfigDesc ');
$pre_cache['ipoman']['out'] = snmpwalk_cache_multi_oid($device, 'outletConfigDesc', $pre_cache['ipoman']['out'], 'IPOMANII-MIB');
d_echo('outletConfigLocation ');
$pre_cache['ipoman']['out'] = snmpwalk_cache_multi_oid($device, 'outletConfigLocation', $pre_cache['ipoman']['out'], 'IPOMANII-MIB');
d_echo('inletConfigDesc ');
$pre_cache['ipoman']['in'] = snmpwalk_cache_multi_oid($device, 'inletConfigDesc', $pre_cache['ipoman']['in'], 'IPOMANII-MIB');
