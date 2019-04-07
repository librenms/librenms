<?php

$temp_data = snmpwalk_cache_oid($device, 'deviceInfo', [], 'ZMTEL-ODU-MIB', 'zmtel');

foreach ($temp_data as $data) {
    $oids     = 'dlMCS ulMCS eNBID pCID';
    $oid_data = array_keys(snmp_getnext_multi($device, $oids, '-OUvq', 'ZMTEL-ODU-MIB'));
    $dlmcs    = $oid_data[0];
    $ulmcs    = $oid_data[1];
    $enbid    = $oid_data[2];
    $pcid     = $oid_data[3];

    $version  = $data['softwareVersion'];
    $hardware = $data['modelName'] . ' ' . $data['hardwareVersion'];
    $serial   = $data['SN'];
    $features = 'eNodeB: ' . $enbid . ' Physical Cell: ' . $pcid . ' DL Modulation: ' . $dlmcs . ' UL Modulation: ' . $ulmcs . ' bootROM: ' . $data['bootROM'] . ' LTE Band: ' . $data['lteBand'];
}
unset($temp_data);
