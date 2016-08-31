<?php

if (!$os) {
    if (strstr($sysDescr, 'TG585v7')) {
        $os = 'speedtouch';
    } elseif (strstr($sysDescr, 'SpeedTouch ')) {
        $os = 'speedtouch';
    } elseif (preg_match('/^ST\d/', $sysDescr)) {
        $os = 'speedtouch';
    }
}
