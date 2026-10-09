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
 * AMD GPU power, from the table of LIBRENMS-AMDGPU-MIB that the 'amdgpu'
 * pass_persist script serves.
 *
 * The net-snmp lmSensors module maps only the temperature, fan and voltage
 * features of libsensors, so GPU watts are given no index at all and cannot
 * reach LM-SENSORS-MIB. The script reads them from the hwmon nodes of the
 * amdgpu driver, one row per card, indexed by its PCI address:
 *
 *     amdGpuProductName[0000:c7:00.0] = AMD Radeon 780M Graphics
 *     amdGpuPower[0000:c7:00.0] = 61006000
 *     amdGpuPowerAverage[0000:c7:00.0] = 50120000
 *
 * A reading the card does not publish has no cell at all rather than a
 * placeholder, so a row that answers for only some of its columns is the
 * ordinary case: on APUs the SMU may decline power1_average while idle.
 */

// The amdgpu_ prefix is deliberate: sensors() includes every discovery
// module into one shared scope, so unprefixed locals would leak into
// the next sensor class.
$amdgpu_table = SnmpQuery::cache()
    ->mibDir('librenms')
    ->hideMib()
    ->walk('LIBRENMS-AMDGPU-MIB::amdGpuTable')
    ->table(1);

if (! empty($amdgpu_table)) {
    echo 'LIBRENMS-AMDGPU-MIB: ' . PHP_EOL;

    // microwatts -> watts
    $amdgpu_divisor = 1000000;

    // amdGpuEntry, and the column each sensor reads from it. The columns are
    // numbered here rather than translated, because a stored sensor OID is
    // numeric and this MIB is not in the search path of a UNIX device.
    $amdgpu_entry = '.1.3.6.1.4.1.60652.101.1.1';
    $amdgpu_power = [
        'amdGpuPower' => [3, 'Power'],
        'amdGpuPowerAverage' => [4, 'Power (average)'],
    ];

    foreach ($amdgpu_table as $amdgpu_pci => $amdgpu_card) {
        // The address stays in the group even when a name is known, so
        // two identical cards in one host remain distinguishable. Kept in
        // step with sensors/frequency/unix.inc.php: should the two name a
        // group differently, one card would land in two groups under Health.
        $amdgpu_product = $amdgpu_card['amdGpuProductName'] ?? '';
        $amdgpu_group = $amdgpu_product === ''
            ? 'GPU ' . $amdgpu_pci
            : $amdgpu_product . ' (' . $amdgpu_pci . ')';

        // The walk hands the address back readable; an OID carries it as
        // its length followed by one sub-identifier per character.
        $amdgpu_index_oid = Oid::encodeString($amdgpu_pci);

        foreach ($amdgpu_power as $amdgpu_object => [$amdgpu_column, $amdgpu_descr]) {
            $amdgpu_value = $amdgpu_card[$amdgpu_object] ?? '';

            if (! is_numeric($amdgpu_value)) {
                continue;
            }

            $amdgpu_oid = $amdgpu_entry . '.' . $amdgpu_column . '.' . $amdgpu_index_oid;

            discover_sensor(
                null,
                'power',
                $device,
                $amdgpu_oid,
                $amdgpu_pci . '.' . $amdgpu_object,
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
