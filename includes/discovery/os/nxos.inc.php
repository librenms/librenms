<?php

use Illuminate\Support\Str;

if (Str::startsWith($device['sysDescr'], 'Cisco NX-OS')) {
    if (Str::startsWith($device['hardware'], 'UCS') || Str::startsWith($device['sysDescr'], 'Cisco NX-OS(tm) ucs')) {
        $os = 'ucos';
    } else {
        $os = 'nxos';
    }
}
