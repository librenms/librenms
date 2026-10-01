<?php

use App\Facades\PortCache;

// ddmiStatusInterfaceIfIndex holds the port number, the matching IF-MIB ifIndex is offset by 1000000
foreach (SnmpQuery::walk('MIB-DDMI::ddmiStatusInterfaceIfIndex')->pluck() as $index => $portNumber) {
    $port = PortCache::getByIfIndex((int) $portNumber + 1000000, $device['device_id']);
    $pre_cache['hyperion_ddmi_port'][$index] = $port->ifDescr ?? "Port $portNumber";
}
