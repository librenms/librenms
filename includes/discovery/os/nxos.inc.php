<?php

use Illuminate\Support\Str;

if (Str::startsWith($device['sysDescr'], 'Cisco NX-OS')) {
    if (Str::startsWith($device['sysObjectID'], '.1.3.6.1.4.1.9.12.3.1.3.1062') || Str::startsWith($device['sysDescr'], 'Cisco NX-OS(tm) ucs')) {
        $os = 'ucos';
    } else {
        $os = 'nxos';
    }
}
