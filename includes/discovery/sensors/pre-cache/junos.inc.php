<?php

if ($device['os'] == 'junos') {

    echo 'Pre-cache JunOS: ';

    $junos_oids = array();
    echo 'Caching OIDs:';

    $junos_oids = snmpwalk_cache_multi_oid($device, 'JnxDomCurrentEntry', $oids, 'JUNIPER-DOM-MIB', $config['mib_dir'].':'.$config['mib_dir'].'/junos');

}
