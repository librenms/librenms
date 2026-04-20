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
    '.1.3.6.1.4.1.20916.1.8.1.2.1' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.8\.1\.2\.((\d+)\.1\.0)/',
    '.1.3.6.1.4.1.20916.1.8.1.2.3' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.8\.1\.2\.((\d+)\.3\.0)/',
    '.1.3.6.1.4.1.20916.1.8.1.3' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.8\.1\.3\.((\d+)\.0)/',
    '.1.3.6.1.4.1.20916.1.8.1.4' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.8\.1\.4\.((\d+)\.4\.1\.2\.0)/',
    '.1.3.6.1.4.1.20916.1.11.1.1.5' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.11\.1\.1\.5\.((\d+)\.0)/',
    '.1.3.6.1.4.1.20916.1.11.1.1.6' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.11\.1\.1\.6\.((\d+)\.0)/',
    '.1.3.6.1.4.1.20916.1.11.1.2.1' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.11\.1\.2\.((\d+)\.1\.0)/',
    '.1.3.6.1.4.1.20916.1.11.1.2.3' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.11\.1\.2\.((\d+)\.3\.0)/',
    '.1.3.6.1.4.1.20916.1.11.1.3' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.11\.1\.3\.((\d+)\.0)/',
    '.1.3.6.1.4.1.20916.1.12.1.1.2' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.12\.1\.1\.2\.((\d+)\.0)/',
    '.1.3.6.1.4.1.20916.1.12.1.1.3' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.12\.1\.1\.3\.((\d+)\.0)/',
    '.1.3.6.1.4.1.20916.1.12.1.2.1' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.12\.1\.2\.((\d+)\.1\.0)/',
    '.1.3.6.1.4.1.20916.1.12.1.2.3' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.12\.1\.2\.((\d+)\.3\.0)/',
    '.1.3.6.1.4.1.20916.1.12.1.3' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.12\.1\.3\.((\d+)\.0)/',
];

$data = SnmpQuery::numeric()->walk('.1.3.6.1.4.1.20916.1')->getRawWithoutBadLines();
foreach (explode(PHP_EOL, (string) $data) as $line) {
    $parts = explode(' =', $line, 2);
    if (count($parts) < 2) {
        continue;
    }
    [$oid, $value] = $parts;
    $value = trim(trim($value), '"');
    $processed = false;
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

// Resolve labels for ext sensor virtual tables
$ext_label_oids = [
    '.1.3.6.1.4.1.20916.1.8.1.2.1' => '.1.3.6.1.4.1.20916.1.8.1.4.2.',
    '.1.3.6.1.4.1.20916.1.8.1.2.3' => '.1.3.6.1.4.1.20916.1.8.1.4.2.',
    '.1.3.6.1.4.1.20916.1.11.1.2.1' => '.1.3.6.1.4.1.20916.1.11.1.4.2.',
    '.1.3.6.1.4.1.20916.1.11.1.2.3' => '.1.3.6.1.4.1.20916.1.11.1.4.2.',
    '.1.3.6.1.4.1.20916.1.11.1.1.6' => '.1.3.6.1.4.1.20916.1.11.1.4.5.',
    '.1.3.6.1.4.1.20916.1.11.1.3' => '.1.3.6.1.4.1.20916.1.11.1.4.3.',
    '.1.3.6.1.4.1.20916.1.12.1.2.1' => '.1.3.6.1.4.1.20916.1.12.1.4.2.',
    '.1.3.6.1.4.1.20916.1.12.1.2.3' => '.1.3.6.1.4.1.20916.1.12.1.4.2.',
    '.1.3.6.1.4.1.20916.1.12.1.1.3' => '.1.3.6.1.4.1.20916.1.12.1.4.5.',
    '.1.3.6.1.4.1.20916.1.12.1.3' => '.1.3.6.1.4.1.20916.1.12.1.4.3.',
];
foreach ($ext_label_oids as $vt_name => $label_base) {
    if (! isset($pre_cache[$vt_name])) {
        continue;
    }
    foreach ($pre_cache[$vt_name] as &$entry) {
        $label_oid = $label_base . $entry['id'] . '.0';
        $label = $pre_cache[$label_oid][0][$label_oid] ?? null;
        if ($label) {
            $entry['label'] = $label;
        }
    }
    unset($entry);
}
