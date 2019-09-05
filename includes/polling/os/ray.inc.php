<?php
//Ray3
$ray_tmp = snmp_get_multi_oid($device, 'productName.0 swVer.0 serialNumber.0 unitType.0', '-OQs', 'RAY-MIB');
$hardware      = $ray_tmp['productName.0'];
$version       = $ray_tmp['swVer.0'];
$serial        = $ray_tmp['serialNumber.0'];
$features      = $ray_tmp['unitType.0'];

//Ray1 and Ray2 has no index
if (empty($hardware)) {
    $ray_tmp = snmp_get_multi_oid($device, 'productName swVer serialNumber unitType', '-OQs', 'RAY-MIB');
    $hardware      = $ray_tmp['productName'];
    $version       = $ray_tmp['swVer'];
    $serial        = $ray_tmp['serialNumber'];
    $features      = $ray_tmp['unitType'];

    $snmpdata = snmp_get_multi_oid($device, ['sysName', 'sysObjectID', 'sysDescr'], '-OUQn', 'SNMPv2-MIB');

    $core_update = array(
        'sysObjectID' => $snmpdata['.1.3.6.1.2.1.1.2'],
        'sysName' => strtolower(trim($snmpdata['.1.3.6.1.2.1.1.5'])),
        'sysDescr' => $snmpdata['.1.3.6.1.2.1.1.1'],
    );

    foreach ($core_update as $item => $value) {
        if ($device[$item] != $value) {
            $device[$item] = $value; // update the device array
            log_event("$item -> $value", $device, 'system', 3);
        } else {
            unset($core_update[$item]);
        }
    }

    if (!empty($core_update)) {
        dbUpdate($core_update, 'devices', 'device_id=?', array($device['device_id']));
    }

    unset($core_update, $snmpdata);
};
unset($ray_tmp);
