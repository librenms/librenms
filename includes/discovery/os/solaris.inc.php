<?php

if (starts_with($sysDescr, 'SunOS')) {
    $os = 'solaris';
    list(,,$version) = explode(' ', $sysDescr);

    if (version_compare($version, '5.10', '>')) {
        if (str_contains($sysDescr, 'oi_')) {
            $os = 'openindiana';
        } else {
            $os = 'opensolaris';
        }
    }
}

if (starts_with($sysObjectId, '.1.3.6.1.4.1.42.2.1.1')) {
    $os = 'solaris';
}
