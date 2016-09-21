<?php

if (starts_with('WatchGuard', $sysDescr) || str_contains('1.3.6.1.4.1.3097.1.5', $sysObjectId)) {
    $os = 'firebox';
}
