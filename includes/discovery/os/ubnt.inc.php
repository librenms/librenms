<?php

use Illuminate\Support\Str;

if (Str::startsWith($device['sysDescr'], 'Linux') || Str::startsWith($device['sysObjectID'], '.1.3.6.1.4.1.8072.3.2.10')) {
    if (Str::startsWith($device['sysObjectID'], array('.1.3.6.1.4.1.10002.1', '.1.3.6.1.4.1.41112.1.4'))
        || Str::contains(snmp_walk($device, 'dot11manufacturerName', '-Osqnv', 'IEEE802dot11-MIB'), 'Ubiquiti')
    ) {
        $os = 'airos';
        if (Str::contains(snmp_walk($device, 'dot11manufacturerProductName', '-Osqnv', 'IEEE802dot11-MIB'), 'UAP')) {
            $os = 'unifi';
        } elseif (snmp_get($device, 'fwVersion.1', '-Osqnv', 'UBNT-AirFIBER-MIB', 'ubnt') !== false) {
            $os = 'airos-af';
        }
    } elseif (snmp_get($device, 'afLTUFirmwareVersion.0', '-Osqnv', 'UBNT-AFLTU-MIB', 'ubnt') !== false) {
        $os = 'airos-af-ltu';
    }
}
