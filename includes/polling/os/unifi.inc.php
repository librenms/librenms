<?php

if ($poll_device['sysObjectID'] == 'enterprises.10002.1') {
    if ($data = snmp_get_multi($device, 'dot11manufacturerProductName.0 dot11manufacturerProductVersion.0', '-OQUs', 'IEEE802dot11-MIB')) {
        $hardware = $data[0]['dot11manufacturerProductName'];
        if (preg_match('/(v[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/', $data[0]['dot11manufacturerProductVersion'], $matches)) {
            $version = $matches[0];
        }
    }
} elseif ($poll_device['sysObjectID'] == 'enterprises.8072.3.2.10') {
    if ($data = snmp_get_multi($device, 'unifiApSystemModel.0 unifiApSystemVersion.0', '-OUQs', 'UBNT-UniFi-MIB')) {
        $hardware = $data[0]['unifiApSystemModel'];
        $version = $data[0]['unifiApSystemVersion'];
    }
}
