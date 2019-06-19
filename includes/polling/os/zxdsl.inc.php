<?php

use LibreNMS\RRD\RrdDefinition;

//$version = trim(snmp_get($device, '1.3.6.1.2.1.1.2.0', '-OQv', '', ''), '"');
if (strstr($device['sysObjectID'],'.1.3.6.1.4.1.3902.')) {
    $sysObjectId = str_replace('.1.3.6.1.4.1.3902.','',$device['sysObjectID']);
    $sysObjectIdSplit = explode('.',$sysObjectId);
    if (count($sysObjectIdSplit) >= 2) {
        $hardware = "ZXDSL ".$sysObjectIdSplit[1];
    }
    unset($sysObjectId);
    unset($sysObjectIdSplit);
}
