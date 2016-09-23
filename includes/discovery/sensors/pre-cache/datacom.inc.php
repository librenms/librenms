<?php

if ($device['os'] == 'datacom') {
    echo 'Pre-cache Datacom: ';

    $datacom_oids = array();
    echo 'Caching OIDs:';

    $datacom_oids = snmpwalk_cache_multi_oid($device, 'ddTransceiversEntry', array(), 'DMswitch-MIB');
}
