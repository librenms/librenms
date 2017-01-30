<?php

if ($device['os'] === 'datacom') {
    $pre_cache['datacom_oids'] = snmpwalk_cache_multi_oid($device, 'ddTransceiversEntry', array(), 'DMswitch-MIB');
    d_echo($pre_cache);
}
