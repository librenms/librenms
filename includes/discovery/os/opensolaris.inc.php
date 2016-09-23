<?php

if (starts_with($sysDescr, 'SunOS')) {
    list(,,$version) = explode(' ', $sysDescr);

    if (version_compare($version, '5.10', '>')) {
        if (!str_contains($sysDescr, 'oi_')) {
            $os = 'opensolaris';
        }
    }
}
