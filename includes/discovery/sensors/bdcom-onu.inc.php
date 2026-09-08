<?php

/**
 * bdcom-onu.inc.php
 *
 * Per-ONU optical RX power for BDCOM EPON/GPON OLTs, labelled with the
 * operator-assigned ONU name (the description configured on the OLT).
 *
 * The RX-power table is keyed by the per-ONU optical ifIndex, but the
 * operator name table is keyed by <parent-PON-port ifIndex>.<registration MAC>,
 * so the two are joined through the per-ONU registration MAC:
 *
 *   RX power : .1.3.6.1.4.1.3320.101.10.5.1.5.<onuIfIndex>            (0.1 dBm, signed)
 *   ONU MAC  : .1.3.6.1.4.1.3320.101.10.1.1.3.<onuIfIndex>           (registration MAC)
 *   ONU name : .1.3.6.1.4.1.3320.101.11.1.1.4.<portIfIndex>.<MAC>    (operator name; "N/A" if unset)
 *
 * Standard YAML os_discovery can only template the sensor description from a
 * column sharing the sensor index, so this join is done here in PHP. When no
 * name is configured (or on models without the EPON name table) the label
 * falls back to the interface name, preserving the previous behaviour.
 *
 * @copyright  2026 SCUD Communication Private Limited, Jalgaon
 */

use App\Models\Port;

$rx_base = '.1.3.6.1.4.1.3320.101.10.5.1.5';
$mac_base = '.1.3.6.1.4.1.3320.101.10.1.1.3';
$name_base = '.1.3.6.1.4.1.3320.101.11.1.1.4';

$rx_values = SnmpQuery::numeric()->walk($rx_base)->pluck($rx_base);

if (! empty($rx_values)) {
    echo 'BDCOM ONU Optical ';

    // onuIfIndex -> registration MAC (12 hex chars, lower-case)
    $ifindex_to_mac = [];
    foreach (SnmpQuery::options(['-OQXUnx', '-Pu'])->walk($mac_base)->pluck($mac_base) as $ifIndex => $raw_mac) {
        $hex = strtolower((string) preg_replace('/[^0-9A-Fa-f]/', '', (string) $raw_mac));
        if (strlen($hex) === 12) {
            $ifindex_to_mac[(int) $ifIndex] = $hex;
        }
    }

    // registration MAC -> operator name (drop the leading parent-port ifIndex)
    $mac_to_name = [];
    foreach (SnmpQuery::numeric()->walk($name_base)->pluck($name_base) as $index => $raw_name) {
        $octets = array_slice(explode('.', (string) $index), -6);
        if (count($octets) !== 6) {
            continue;
        }
        $hex = '';
        foreach ($octets as $o) {
            $hex .= sprintf('%02x', (int) $o);
        }
        $name = trim((string) $raw_name, ' "');
        if ($name !== '' && strcasecmp($name, 'N/A') !== 0) {
            $mac_to_name[$hex] = $name;
        }
    }

    // onuIfIndex -> ifName (ports are already discovered before sensors)
    $ifnames = Port::where('device_id', $device['device_id'])->pluck('ifName', 'ifIndex')->all();

    foreach ($rx_values as $ifIndex => $raw_value) {
        $ifIndex = (int) $ifIndex;
        $raw = (int) $raw_value;
        if (in_array($raw, [0, 65535, -65535], true)) {
            continue; // no ONU / no reading
        }

        $ifName = $ifnames[$ifIndex] ?? ('ifIndex ' . $ifIndex);
        $mac = $ifindex_to_mac[$ifIndex] ?? null;
        $name = ($mac !== null && isset($mac_to_name[$mac])) ? $mac_to_name[$mac] : null;

        $descr = $name !== null ? ($ifName . ' — ' . $name) : ($ifName . ' ONU RX');
        $num_oid = $rx_base . '.' . $ifIndex;

        discover_sensor(null, 'dbm', $device, $num_oid, 'bdcomOnuRx.' . $ifIndex, 'bdcom', $descr, 10, 1, null, null, null, null, $raw / 10, 'snmp', null, null, null, 'ONU Optical');
    }

    unset($ifindex_to_mac, $mac_to_name, $ifnames);
}

unset($rx_values, $rx_base, $mac_base, $name_base);
