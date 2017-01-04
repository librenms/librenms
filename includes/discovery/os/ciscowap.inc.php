<?php

if (str_contains($sysDescr, 'Cisco Small Business WAP')) {
    $os = 'ciscowap';
}

if (starts_with($sysObjectId, '.1.3.6.1.4.1.9.6.1.32.321.1')) {
    $os = 'ciscowap';
}
