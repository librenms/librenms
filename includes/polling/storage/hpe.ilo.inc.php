<?php

if (!is_array($storage_cache1['ilo-storage'])) {
    $storage_cache1['ilo-storage'] = snmpwalk_cache_oid($device, 'cpqHoFileSysEntry', null, 'CPQHOST-MIB', 'hpe-ilo');
    echo("HPE ILO4 ");
}

$iind = 0;
$storage_cache10 = array();

echo($storage);

foreach ($storage_cache1['ilo-storage'] as $index => $ventry) {
    if (!array_key_exists('cpqHoFileSysDesc', $ventry)) {
        continue;
    }
    if (is_int($index)) {
        $iind = $index;
    } else {
        $arrindex = explode(".", $index);
        $iind = (int)(end($arrindex))+0;
    }
    if (is_int($iind)) {
        $storage_cache10[$iind] = $ventry;
    }
}
echo($storage_cache10);

$entry1 = $storage_cache10[$storage[storage_index]];

$storage['units'] = 1024*1024;
$storage['size'] = ($entry1['cpqHoFileSysSpaceTotal'] * $storage['units']);
$storage['used'] = ($entry1['cpqHoFileSysSpaceUsed'] * $storage['units']);
$storage['free'] = ($storage['size'] - $storage['used']);
