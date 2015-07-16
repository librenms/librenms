<?php

if (!$os) {
    if (strstr($sysDescr, 'Apple AirPort')) {
        $os = 'airport';
    }
    if (strstr($sysDescr, 'Apple Base Station')) {
        $os = 'airport';
    }
    if (strstr($sysDescr, 'Base Station V3.84')) {
        $os = 'airport';
    }
}
