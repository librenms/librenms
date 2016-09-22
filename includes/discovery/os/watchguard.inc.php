<?php

if (starts_with('WatchGuard Fireware', $sysDescr) || starts_with('.1.3.6.1.4.1.3097.1.5', $sysObjectId)) {
    $os = 'firebox';
}
