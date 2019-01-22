<?php
if (preg_match('/^(FB[0-9]{4}).*\((.*)\).*$/m', $device['sysDescr'], $regexp_result)) {
    $hardware = $regexp_result[1];
    $version = $regexp_result[2];
}
