<?php

if ($data = snmp_get_multi($device, 'unifiApSystemModel unifiApSystemVersion', '-OUQs', 'UBNT-UniFi-MIB')) {
    $hardware = $data[0]['unifiApSystemModel'];
    $version = $data[0]['unifiApSystemVersion'];
} elseif ($data = snmp_get_multi($device, 'dot11manufacturerProductName dot11manufacturerProductVersion', '-OQUs', 'IEEE802dot11-MIB')) {
    $hardware = $data[0]['dot11manufacturerProductName'];
    if (preg_match('/(v[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/', $data[0]['dot11manufacturerProductVersion'], $matches)) {
        $version = $matches[0];
    }
}
