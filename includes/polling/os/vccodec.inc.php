<?php

// Cisco CodecSoftW: ce8.1.0.b8c0ca3MCU: Cisco TelePresence SX20Date: 2016-04-05S/N: 0101010101
if (preg_match('/^Cisco Codec\s?SoftW: ([^,]+)\s?MCU: Cisco TelePresence ([^,]+)\s?Date: [^,]+\s?S\/N: ([^,]+)$/', $device['sysDescr'], $regexp_result)) {
    $version = $regexp_result[1];
    $hardware = $regexp_result[2];
    $serial = $regexp_result[3];
}
