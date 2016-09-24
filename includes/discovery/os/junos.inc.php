<?php

if (starts_with($sysObjectId, '.1.3.6.1.4.1.2636')) {
    $os = 'junos';
}

if (str_contains($sysDescr, 'kernel JUNOS')) {
    $os = 'junos';
}
