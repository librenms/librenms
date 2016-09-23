<?php

if (starts_with($sysDescr, 'SunOS')) {
    list(,,$version) = explode(' ', $sysDescr);

    if (version_compare($version, '5.10', '>')) {
        if (str_contains($sysDescr, 'oi_')) {
            $os = 'openindiana';
        }
    }
}

if (starts_with($sysObjectId, '.1.3.6.1.4.1.42.2.1.1')) {
    $os = 'solaris';
}
