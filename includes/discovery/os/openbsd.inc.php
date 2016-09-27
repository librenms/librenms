<?php

if (starts_with($sysObjectId, '.1.3.6.1.4.1.30155.23.1') || str_contains($sysDescr, 'OpenBSD')) {
    $os = 'openbsd';
}
