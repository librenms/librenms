<?php

$zhone_interface_translations = snmpwalk_cache_oid($device, 'zhonePhysicalIfIndex', [], 'ZHONE-INTERFACE-TRANSLATION-MIB');

$good_ifs = [];

foreach ($zhone_interface_translations as $key => $value) {
    $type = explode('.', $key)[4];
    $subtype = explode('.', $key)[5];
    if ($type == 'other') {
        $type = $subtype;
    }
    $index = $value['zhonePhysicalIfIndex'];
    $port_stats[$index]['ifType'] = $type;
    $good_ifs[$index] = $port_stats[$index];
}

$port_stats = $good_ifs;
