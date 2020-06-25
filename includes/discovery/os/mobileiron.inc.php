<?php

use Illuminate\Support\Str;

// At ths time, MI don't make any customisations to the tree, so we just detect their packages
if (Str::startsWith($device['sysDescr'], 'Linux') && Str::startsWith($device['sysObjectID'], '.1.3.6.1.4.1.8072.3.2.10')) {
    if (Str::contains(snmp_walk($device, 'hrSWInstalledName', '-Osqnv', 'HOST-RESOURCES-MIB'), 'mobileiron')) {
        $os = 'mobileiron';
    }
}
