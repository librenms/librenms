<?php
if ($data = snmp_getnext_multi($device, 'unifiApSystemModel unifiApSystemVersion', '-OQUs', 'UBNT-UniFi-MIB')) {
    $hardware = $data['unifiApSystemModel'];
    $version = $data['unifiApSystemVersion'];
} elseif ($data = snmp_getnext_multi($device, 'dot11manufacturerProductName dot11manufacturerProductVersion', '-OQUs', 'IEEE802dot11-MIB')) {
    $hardware = $data['dot11manufacturerProductName'];
    if (preg_match('/(v[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/', $data['dot11manufacturerProductVersion'], $matches)) {
        $version = $matches[0];
    }
}
