<?php

echo 'ddTransceiversEntry ';
$pre_cache['datacom_oids'] = snmpwalk_cache_multi_oid($device, 'ddTransceiversEntry', [], 'DMswitch-MIB');
