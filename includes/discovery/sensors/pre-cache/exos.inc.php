<?php

echo 'connUnitSensorTable ';
$pre_cache['exos']['connUnitSensorTable'] = snmpwalk_cache_multi_oid($device, 'connUnitSensorTable', [], 'FCMGMT-MIB', null, '-OteQUsab');
