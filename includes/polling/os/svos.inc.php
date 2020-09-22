<?php

$data = snmpwalk_cache_oid($device, 'raidExMibRaidListTable', [], 'HM800MIB');
d_echo($data);

foreach ($data as $serialnum => $oid) {
    if (isset($data[$serialnum]['raidlistSerialNumber']) && $data[$serialnum]['raidlistSerialNumber'] != '') {
        $serial = $data[$serialnum]['raidlistSerialNumber'];
    }

    if (isset($data[$serialnum]['raidlistDKCProductName']) && $data[$serialnum]['raidlistDKCProductName'] != '') {
        $hardware = $data[$serialnum]['raidlistDKCProductName'];
    }

    if (isset($data[$serialnum]['raidlistDKCMainVersion']) && $data[$serialnum]['raidlistDKCMainVersion'] != '') {
        $version = $data[$serialnum]['raidlistDKCMainVersion'];
    }
}
