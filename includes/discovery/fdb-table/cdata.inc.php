<?php

use Illuminate\Support\Facades\Log;
use LibreNMS\OS\Cdata;
use LibreNMS\Util\Mac;

/**
 * C-Data OLTs have no BRIDGE-MIB forwarding table, only a vendor one indexed by 6.<mac>.<vlan>.
 * Uplink entries point at an ifIndex, subscriber entries at an ONU index; those land on the PON port the ONU is on.
 */
echo 'C-Data: ';

/** @var Cdata $os */
$onuPonPorts = $os->onuPonPorts();

foreach (SnmpQuery::cache()->numeric()->walk(Cdata::FDB_PORT_COLUMN)->values() as $oid => $target) {
    if (! preg_match('/\.6((?:\.\d+){6})\.(\d+)$/', substr($oid, strlen(Cdata::FDB_PORT_COLUMN)), $m)) {
        continue;
    }
    $mac_address = Mac::parse(implode(':', array_map(fn ($o) => sprintf('%02x', $o), explode('.', trim($m[1], '.')))))->hex();
    $vlan = (int) $m[2];

    $ifIndex = $onuPonPorts[$target] ?? $target;
    $port_id = PortCache::getIdFromIfIndex($ifIndex, $device['device_id']);
    if (! $port_id) {
        Log::debug("No port known for $mac_address (target $target)\n");
        continue;
    }

    $vlan_id = $vlans_dict[$vlan] ?? 0;
    $insert[$vlan_id][$mac_address]['port_id'] = $port_id;
    Log::debug("vlan $vlan mac $mac_address port $port_id\n");
}

unset($onuPonPorts, $oid, $target, $m, $mac_address, $vlan, $ifIndex, $port_id, $vlan_id);
