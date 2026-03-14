<?php

/**
 * avtech.inc.php
 *
 * Grab all data under avtech enterprise oid and process it for yaml consumption
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

// table name => regex (first group is index, second group is id)
$virtual_tables = [
    '.1.3.6.1.4.1.20916.1.8.1.1.5' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.8\.1\.1\.5\.((\d+)\.0)/',
    '.1.3.6.1.4.1.20916.1.8.1.1.6' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.8\.1\.1\.6\.((\d+)\.0)/',
    '.1.3.6.1.4.1.20916.1.8.1.2' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.8\.1\.2\.((\d+)\.1\.0)/',
    '.1.3.6.1.4.1.20916.1.8.1.3' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.8\.1\.3\.((\d+)\.0)/',
    '.1.3.6.1.4.1.20916.1.8.1.4' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.8\.1\.4\.((\d+)\.4\.1\.2\.0)/',
    '.1.3.6.1.4.1.20916.1.11.1.1.5' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.11\.1\.1\.5\.((\d+)\.0)/',
    '.1.3.6.1.4.1.20916.1.11.1.1.6' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.11\.1\.1\.6\.((\d+)\.0)/',
    '.1.3.6.1.4.1.20916.1.11.1.2' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.11\.1\.2\.((\d+)\.1\.0)/',
    '.1.3.6.1.4.1.20916.1.11.1.3' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.11\.1\.3\.((\d+)\.0)/',
    'ra12s-analog' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.12\.1\.1\.2\.((\d+)\.0)/',
    'ra12s-relay' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.12\.1\.1\.3\.((\d+)\.0)/',
    'ra12s-ext-temp' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.12\.1\.2\.((\d+)\.1\.0)/',
    'ra12s-switch' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.12\.1\.3\.((\d+)\.0)/',
];
$data = SnmpQuery::numeric()->walk('.1.3.6.1.4.1.20916.1')->getRawWithoutBadLines();
foreach (explode(PHP_EOL, (string) $data) as $line) {
    $parts = explode(' =', $line, 2);
    if (count($parts) < 2) {
        continue;
    }
    [$oid, $value] = $parts;
    $value = trim(trim($value), '"');    $processed = false;
    foreach ($virtual_tables as $vt_name => $vt_regex) {
        if (preg_match($vt_regex, $oid, $matches)) {
            $index = $matches[1];
            $id = $matches[2];
            $pre_cache[$vt_name][$index] = ['value' => $value, 'id' => $id];
            $processed = true;
            break;
        }
    }
    if (! $processed) {
        $pre_cache[$oid] = [[$oid => $value]];
    }
}
unset($data);

// RoomAlert MAX
// Decode OID-encoded string index: <length>.<ascii1>.<ascii2>... -> string
$ramax_oid_to_string = function (string $oid_suffix): string {
    $parts = explode('.', $oid_suffix);
    $len = (int) array_shift($parts);

    return implode('', array_map(chr(...), array_slice($parts, 0, $len)));
};

// Walk sensor names, keyed by MAC address string e.g. "00:BE:44:EA:3E:CA"
$sensor_names = [];
$sensor_data = SnmpQuery::numeric()->walk('.1.3.6.1.4.1.20916.1.14.1.1.1.2')->getRawWithoutBadLines();
if ($sensor_data) {
    foreach (explode(PHP_EOL, (string) $sensor_data) as $line) {
        $parts = explode(' =', $line, 2);
        if (count($parts) < 2) {
            continue;
        }
        [$oid, $value] = $parts;
        $value = trim(trim($value), '"');
        if (preg_match('/\.1\.3\.6\.1\.4\.1\.20916\.1\.14\.1\.1\.1\.2\.(.+)/', $oid, $m)) {
            $mac_parts = explode('.', $m[1]);
            $mac = implode(':', array_map(fn ($b) => strtoupper(sprintf('%02X', $b)), $mac_parts));
            $sensor_names[$mac] = $value;
        }
    }
}
unset($sensor_data);

// Walk channel group labels, keyed by decoded group key string
$group_labels = [];
$group_data = SnmpQuery::numeric()->walk('.1.3.6.1.4.1.20916.1.14.2.1.1.3')->getRawWithoutBadLines();
if ($group_data) {
    foreach (explode(PHP_EOL, (string) $group_data) as $line) {
        $parts = explode(' =', $line, 2);
        if (count($parts) < 2) {
            continue;
        }
        [$oid, $value] = $parts;
        $value = trim(trim($value), '"');
        if (preg_match('/\.1\.3\.6\.1\.4\.1\.20916\.1\.14\.2\.1\.1\.3\.(.+)/', $oid, $m)) {
            $group_labels[$ramax_oid_to_string($m[1])] = $value;
        }
    }
}
unset($group_data);

// Walk channel fields: 2=groupRef, 3=type, 4=value, 5=description
foreach ([2, 3, 4, 5] as $field) {
    $field_data = SnmpQuery::numeric()->walk('.1.3.6.1.4.1.20916.1.14.3.1.1.' . $field)->getRawWithoutBadLines();
    if (! $field_data) {
        continue;
    }
    foreach (explode(PHP_EOL, (string) $field_data) as $line) {
        $parts = explode(' =', $line, 2);
        if (count($parts) < 2) {
            continue;
        }
        [$oid, $value] = $parts;
        $value = trim(trim($value), '"');
        if (preg_match('/\.1\.3\.6\.1\.4\.1\.20916\.1\.14\.3\.1\.1\.' . $field . '\.(.+)/', $oid, $m)) {
            $pre_cache['ramax-channels'][$m[1]][$field] = $value;
        }
    }
    unset($field_data);
}

// Resolve description for each channel using sensor name + group label
// channelGroupReference (field 2) format: baseMac_sensorMac_groupType_groupIndex
$generic_labels = ['Temperature & Humidity', 'Wireless', 'Temperature', 'Humidity'];
foreach (array_keys($pre_cache['ramax-channels'] ?? []) as $index) {
    $channel = &$pre_cache['ramax-channels'][$index];
    $group_ref = $channel[2] ?? '';
    $ref_parts = explode('_', $group_ref);
    $sensor_mac = $ref_parts[1] ?? '';
    $sensor_name = $sensor_names[$sensor_mac] ?? null;
    $group_label = $group_labels[$group_ref] ?? null;
    $channel_type = $channel[3] ?? 'Sensor';

    // Use group label as suffix if it's more specific than just the channel type
    $suffix = ($group_label && ! in_array($group_label, $generic_labels))
        ? $group_label
        : $channel_type;

    if ($sensor_name === 'Internal Sensor') {
        $channel['label'] = 'Internal ' . $channel_type;
    } elseif ($sensor_name) {
        $channel['label'] = $sensor_name . ' ' . $suffix;
    } elseif ($group_label && ! in_array($group_label, $generic_labels)) {
        $channel['label'] = $group_label;
    } else {
        $channel['label'] = $channel_type;
    }
    unset($channel);
}
unset($group_labels, $sensor_names);
