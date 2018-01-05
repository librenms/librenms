<?php

/**
 * Some of the processing logic below is borrowed from snmp_get_multi() to parse a multi-output snmp_getnext()
 */

if ($data = snmp_get_multi($device, 'unifiApSystemModel unifiApSystemVersion', '-OQUs', 'UBNT-UniFi-MIB')) {
    $hardware = $data['unifiApSystemModel'];
    $version = $data['unifiApSystemVersion'];
} elseif ($data = snmp_getnext($device, 'dot11manufacturerProductName dot11manufacturerProductVersion', '-Oqs', 'IEEE802dot11-MIB')) {
    foreach (explode("\n", $data) as $entry) {
        $entry = explode(" ", $entry);
        if (str_contains($entry[0], 'dot11manufacturerProductName')) {
            $hardware = $entry[1];
        }
        if (str_contains($entry[0], 'dot11manufacturerProductVersion') && preg_match('/(v[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/', $entry[1], $matches)) {
            $version = $matches[0];
        }
    }
}
