<?php

if (preg_match('/Acano Server ([^,]+)/', $device['sysDescr'], $regexp_result)) {
    $version  = $regexp_result[1];
} else {
    $version = '';
}
