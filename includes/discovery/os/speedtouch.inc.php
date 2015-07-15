<?php

if (!$os) {
    if (strstr($sysDescr, 'TG585v7')) {
        $os = 'speedtouch';
    }
    else if (strstr($sysDescr, 'SpeedTouch ')) {
        $os = 'speedtouch';
    }
    else if (preg_match('/^ST\d/', $sysDescr)) {
        $os = 'speedtouch';
    }
}
