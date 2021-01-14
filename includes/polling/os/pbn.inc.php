<?php

if (preg_match('/^Pacific Broadband Networks .+\n.+ Version ([^,]+), .+\n.+\n.+\nSerial num:([^,]+), .+/', $device['sysDescr'], $regexp_result)) {
    $version = $regexp_result[1];
    $serial = $regexp_result[2];
}
