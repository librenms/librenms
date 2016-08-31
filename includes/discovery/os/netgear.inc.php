<?php

if (!$os) {
    if (stristr($sysDescr, 'ProSafe')) {
        $os = 'netgear';
    } elseif (strpos($sysObjectId, '1.3.6.1.4.1.4526') !== false) {
        $os = 'netgear';
    }
}
