<?php

/**
 * unix.inc.php
 *
 * LibreNMS power sensor discovery module for UNIX based OS
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 */

use LibreNMS\Util\Oid;

/*
 * AMD GPU power, via the 'amdgpu' SNMP extend script.
 *
 * The net-snmp lmSensors module maps only the temperature, fan and voltage
 * features of libsensors, so GPU watts are given no index at all and cannot
 * reach LM-SENSORS-MIB. The extend script reads them from sysfs.
 *
 * Four lines per card, the first identifying it and the rest bare numbers so
 * that each sensor OID resolves to a value the poller reads directly:
 *
 *     0000:c7:00.0 AMD Radeon 780M   PCI address, optionally a product name
 *     30215000                       power1_average, microwatts
 *     40067000                       power1_input, microwatts
 *     2700000000                     freq1_input, hertz  (frequency class)
 *
 * An unreadable value arrives as an empty line and is skipped here, so the
 * block length is constant and later lines are never read as the wrong metric.
 */
$amdgpu_data = SnmpQuery::cache()->hideMib()->walk('NET-SNMP-EXTEND-MIB::nsExtendOutLine."amdgpu"')->table(3);

if (! empty($amdgpu_data)) {
    echo 'AMD GPU: ' . PHP_EOL;
    $amdgpu_data = array_shift($amdgpu_data); // drop the [amdgpu] level

    $amdgpu_lines = [];
    foreach ($amdgpu_data as $amdgpu_index => $amdgpu_line) {
        $amdgpu_lines[(int) $amdgpu_index] = trim((string) ($amdgpu_line['nsExtendOutLine'] ?? ''));
    }

    // offset within the card block => description
    $amdgpu_power = [
        1 => 'Power (average)',
        2 => 'Power',
    ];

    // microwatts -> watts
    $amdgpu_divisor = 1000000;

    for ($amdgpu_base = 1; isset($amdgpu_lines[$amdgpu_base]); $amdgpu_base += 4) {
        [$amdgpu_pci, $amdgpu_product] = array_pad(explode(' ', $amdgpu_lines[$amdgpu_base], 2), 2, '');

        // Verify the block really starts where we think it does, rather than
        // trusting line arithmetic against whatever the extend returned.
        if (! preg_match('/^[0-9a-f]{4}:[0-9a-f]{2}:[0-9a-f]{2}\.\d$/i', $amdgpu_pci)) {
            continue;
        }

        // The address stays in the group even when a name is known, so two
        // identical cards in one host remain distinguishable.
        $amdgpu_group = trim($amdgpu_product) === ''
            ? 'GPU ' . $amdgpu_pci
            : trim($amdgpu_product) . ' (' . $amdgpu_pci . ')';

        foreach ($amdgpu_power as $amdgpu_offset => $amdgpu_descr) {
            $amdgpu_index = $amdgpu_base + $amdgpu_offset;
            $amdgpu_value = $amdgpu_lines[$amdgpu_index] ?? null;

            if (! is_numeric($amdgpu_value)) {
                continue;
            }

            $oid = Oid::of('NET-SNMP-EXTEND-MIB::nsExtendOutLine."amdgpu".' . $amdgpu_index)->toNumeric();
            discover_sensor(
                null,
                'power',
                $device,
                $oid,
                $amdgpu_pci . '.' . $amdgpu_offset,
                'amdgpu',
                $amdgpu_descr,
                $amdgpu_divisor,
                1,
                null,
                null,
                null,
                null,
                $amdgpu_value / $amdgpu_divisor,
                'snmp',
                null,
                null,
                null,
                $amdgpu_group
            );
        }
    }
}
