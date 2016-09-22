<?php

if (starts_with($sysDescr, 'NetMan') && str_contains($sysDescr, 'plus')) {
    $os = 'netmanplus';
}

if (starts_with($sysObjectId, '.1.3.6.1.4.1.5491.6')) {
    $os = 'netmanplus';
}
