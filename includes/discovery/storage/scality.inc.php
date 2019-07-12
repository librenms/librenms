<?php
/**
 * scality.inc.php
 */
use LibreNMS\Config;

if ($device['os'] === 'scality') {
    $scality_storage = snmpwalk_cache_oid($device, 'ringEntry', null, 'SCALITY-MIB');  // grabs OIDs under 'ringEntry'
    if (is_array($scality_storage)) {
        echo 'ringEntry';
        foreach ($scality_storage as $index => $scality) {                                                           // Gets index
            if (is_numeric($scality['ringStorageTotal'])) {
                $units = 1000;
                $fstype = "dsk";
                $descr = $scality['ringName'];
                $size = (($scality['ringStorageTotal']) * $units);
                $used = (($scality['ringStorageUsed']) * $units);
                echo ($state);
                discover_storage($valid_storage, $device, $index, $fstype, 'scality', $descr, $size, $units, $used);
                $ringstate = ($scality['ringState']);
            } // end of if ringStorageTotal
        } // end of for
    } // end of if ringEntry exist
}
