<?php

if (strstr($device['sysObjectID'], '.1.3.6.1.4.1.3902.')) {
    $sysObjectId = str_replace('.1.3.6.1.4.1.3902.', '', $device['sysObjectID']);
    preg_match(
        '/.1.3.6.1.4.1.3902.(1004|1015).(.*).1.1.1/',
        $device['sysObjectID'],
        $matches,
        PREG_OFFSET_CAPTURE
    );

    $sysObjectId = $matches[2][0];
    $sysObjectIdSplit = explode('.', $sysObjectId);

    if (count($sysObjectIdSplit) >= 1) {
        $hardware = 'ZXDSL ' . $sysObjectIdSplit[0];
        if (count($sysObjectIdSplit) >= 2) {
            for ($i = 1; $i < count($sysObjectIdSplit); $i++) {
                $hardware .= chr(64 + $sysObjectIdSplit[$i]);
            }
        }
    }
    unset($matches);
    unset($sysObjectId);
    unset($sysObjectIdSplit);
}
