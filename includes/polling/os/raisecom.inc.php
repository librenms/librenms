<?php

if (preg_match('/^ROAP  Version ([^,]+)/', $device['sysDescr'], $regexp_result)) {
    $version = $regexp_result[1];
    #$serial  = $regexp_result[2];
}
