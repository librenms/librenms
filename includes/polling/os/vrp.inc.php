<?php

//Huawei VRP devices are not providing the HW description in a unified way
// 
//We try from SysDescr, as most of the information is already there. 
//if we succeed, then we don't even have to do an snmp request
//if we fail, we try a few OIDs

preg_match("/Version [^\s]*/m", $device['sysDescr'], $matches);
$version = trim(str_replace('Version ', '', $matches[0]));

preg_match("/\(([^\s]*) (V[0-9]{3}R[0-9]{3}[0-9A-Z]+)/m", $device['sysDescr'], $matches);

if (!empty($matches[2])) {
    $version .= " (" . trim($matches[2]) . ")";
}

if (!empty($matches[1])) {
    $hardware = "Huawei " . trim($matches[1]);
} else {
    // The HW cannot be extracted from sysdescr, let's try OIDs
    $oidList[] = '.1.3.6.1.4.1.2011.2.33.20.1.1.1.3.0';
    $oidList[] = '.1.3.6.1.4.1.2011.5.25.188.1.4.0';
    $oidList[] = '.1.3.6.1.4.1.2011.5.25.183.1.25.1.5.1';
    $oidList[] = '.1.3.6.1.4.1.2011.5.25.31.6.5.0';
    foreach ($oidList as $oid) {
        $hardware_tmp = snmp_get($device, $oid, '-OQv');
        if (!empty($hardware_tmp)) {
            $hardware = "Huawei " . $hardware_tmp;
            break;
        }
    }
}
