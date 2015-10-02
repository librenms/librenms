<?php

if (!$os) {
    if (preg_match('/^WatchGuard\ Fireware/', $sysDescr) || strpos($sysObjectId, '1.3.6.1.4.1.3097.1.5') !== false) {
        $os = 'firebox';
    }
}
