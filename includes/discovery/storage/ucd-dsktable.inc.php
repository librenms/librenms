<?php

$dsktable_array = snmpwalk_cache_oid($device, 'dskTable', null, 'UCD-SNMP-MIB');

$sql = "SELECT `storage_descr` FROM `storage` WHERE `device_id`  = '" . $device['device_id'] . "' AND `storage_type` != 'dsk'";
$tmp_storage = dbFetchColumn($sql);

if (is_array($dsktable_array)) {
    foreach ($dsktable_array as $dsk) {
        if (isset($dsk['dskPath'])) {
            if (! in_array($dsk['dskPath'], $tmp_storage)) {
                $dsk['dskTotal'] = $dsk['dskTotal'] * 1024;
                $dsk['dskAvail'] = ($entry['dskAvail'] * 1024);
                $dsk['dskUsed'] = $dsk['dskTotal'] - $dsk['dskAvail'];

                discover_storage($valid_storage, $device, $dsk['dskIndex'], 'dsk', 'ucd-dsktable', $dsk['dskPath'], $dsk['dskTotal'], 1024, $dsk['dskUsed']);
            }
        }
    }
}
