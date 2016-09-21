<?php

if (str_contains($sysDescr, array('TG585v7', 'SpeedTouch ')) || starts_with('ST', $sysDescr)) {
    $os = 'speedtouch';
}
