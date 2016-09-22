<?php

if (starts_with($sysDescr, array('SunOS'))) {
    $os = 'solaris';
    list(,,$version) = explode(' ', $sysDescr);

    if (version_compare($version, '5.10', '>')) {
        if (str_contains('oi_', $sysDescr)) {
            $os = 'openindiana';
        } else {
            $os = 'opensolaris';
        }
    }
}

if (starts_with('.1.3.6.1.4.1.42.2.1.1', $sysObjectId)) {
    $os = 'solaris';
}
