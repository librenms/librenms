<?php

// ArubaOS (MODEL: Aruba3600), Version 6.1.2.2 (29541)
$badchars                    = array( '(', ')', ',',);
list(,,$hardware,,$version,) = str_replace($badchars, '', explode(' ', $device['sysDescr']));

// Build SNMP Cache Array
// stuff about the controller
$switch_info_oids = array(
    'wlsxSwitchRole',
    'wlsxSwitchMasterIp',
);
echo 'Caching Oids: ';
foreach ($switch_info_oids as $oid) {
    echo "$oid ";
    $aruba_info = snmpwalk_cache_oid($device, $oid, $aruba_info, 'WLSX-SWITCH-MIB');
}

echo "\n";

if ($aruba_info[0]['wlsxSwitchRole'] == 'master') {
    $features = 'Master Controller';
} else {
    $features = 'Local Controller for '.$aruba_info[0]['wlsxSwitchMasterIp'];
}
