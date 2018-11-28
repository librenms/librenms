<?php

if (preg_match('/(.*), iBOS Version ibos-.*?-(.*)\s+Copyright/', $device['sysDescr'], $regexp_result)) {
    $hardware = $regexp_result[1];
    $version  = $regexp_result[2];
}
