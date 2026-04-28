<?php

echo 'Hyperion DDMI dBm: ';

/*
 * ==========================
 * DDMI TABLES
 * ==========================
 */
$ddmi = snmpwalk_cache_oid(
    $device,
    'ddmiStatusInterfaceTable',
    [],
    'MIB-DDMI',
    null,
    '-OQUs'
);

$ifindex_map = snmpwalk_cache_oid(
    $device,
    'ddmiStatusInterfaceIfIndex',
    [],
    'MIB-DDMI',
    null,
    '-OQUs'
);

$ifdescr = snmpwalk_cache_oid(
    $device,
    'ifDescr',
    [],
    'IF-MIB',
    null,
    '-OQUs'
);

/*
 * ==========================
 * LOOP: ONLY SFP PORTS
 * ==========================
 */
foreach ($ddmi as $index => $entry) {

    // ✅ TYLKO porty z wykrytym SFP
    if (($entry['ddmiStatusInterfaceA0SfpDetected'] ?? 'false') != 'true') {
        continue;
    }

    $ddmi_if = (int)($ifindex_map[$index]['ddmiStatusInterfaceIfIndex'] ?? 0);
    if ($ddmi_if <= 0) {
        continue;
    }

    // ✅ mapowanie DDMI -> IF-MIB
    $real_ifIndex = $ddmi_if + 1000000;

    $portDescr = $ifdescr[$real_ifIndex]['ifDescr'] ?? "Port $ddmi_if";

    /*
     * ==========================
     * TX POWER
     * ==========================
     */
    if (!empty($entry['ddmiStatusInterfaceA2CurrentTxPower'])) {

        $value = str_replace(',', '.', trim($entry['ddmiStatusInterfaceA2CurrentTxPower']));
        if (is_numeric($value)) {

            discover_sensor(
                null,
                'dbm',
                $device,
                '.1.3.6.1.4.1.19829.1.121.1.3.2.1.1018.' . $index,
                'ddmiStatusInterfaceA2CurrentTxPower.' . $index,
                'optic_module_power',
                ' TX Power',
                1,
                1,
                null,
                null,
                null,
                null,
                (float)$value,
                'snmp',
                null,
                null,
                null,
                $portDescr
            );
        }
    }

    /*
     * ==========================
     * RX POWER
     * ==========================
     */
    if (!empty($entry['ddmiStatusInterfaceA2CurrentRxPower'])) {

        $value = str_replace(',', '.', trim($entry['ddmiStatusInterfaceA2CurrentRxPower']));
        if (is_numeric($value)) {

            discover_sensor(
                null,
                'dbm',
                $device,
                '.1.3.6.1.4.1.19829.1.121.1.3.2.1.1023.' . $index,
                'ddmiStatusInterfaceA2CurrentRxPower.' . $index,
                'optic_module_power',
                ' RX Power',
                1,
                1,
                null,
                null,
                null,
                null,
                (float)$value,
                'snmp',
                null,
                null,
                null,
                $portDescr
            );
        }
    }
}
