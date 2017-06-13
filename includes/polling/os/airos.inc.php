<?php

foreach (array(10,5) as $i) {
    $result = snmp_get_multi_oid($device, "dot11manufacturerProductName.$i dot11manufacturerProductVersion.$i", '-OQUs', 'IEEE802dot11-MIB');

    // If invalid, $result contains one empty element.
    // So we have to verify it contains exactly two elements.
    if (count($result) == 2) {
        $hardware = 'Ubiquiti ' . $result["dot11manufacturerProductName.$i"];
        $version  = $result["dot11manufacturerProductVersion.$i"];
        list(, $version) = preg_split('/\.v/', $version);
        break;
    }
}

unset($result);

// EOF
