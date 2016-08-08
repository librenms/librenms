<?php

if (!$os) {
    if (strstr($sysObjectId, '.1.3.6.1.4.1.2636')) {
        $os = 'junos';
    } elseif (stristr($sysDescr, 'kernel JUNOS')) {
        $os = 'junos';
    }
}
