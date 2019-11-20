<?php

$snmpdata = snmp_get_multi_oid($device, ['sysName.0', 'sysObjectID.0', 'sysDescr.0'], '-OUQn', 'SNMPv2-MIB');

$core_update = array(
    'sysObjectID' => $snmpdata['.1.3.6.1.2.1.1.2.0'],
    'sysName' => strtolower(trim($snmpdata['.1.3.6.1.2.1.1.5.0'])),
    'sysDescr' => $snmpdata['.1.3.6.1.2.1.1.1.0'],
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
