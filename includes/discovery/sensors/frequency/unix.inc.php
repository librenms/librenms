<?php

/**
 * unix.inc.php
 *
 * LibreNMS frequency sensor discovery module for UNIX based OS
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
 * AMD GPU core clock, from the table of LIBRENMS-AMDGPU-MIB that the 'amdgpu'
 * pass_persist script serves.
 *
 * The net-snmp lmSensors module maps only the temperature, fan and voltage
 * features of libsensors, so the GPU clock is given no index at all and cannot
 * reach LM-SENSORS-MIB. The script reads it from the hwmon nodes of the amdgpu
 * driver, one row per card, indexed by its PCI address:
 *
 *     amdGpuProductName[0000:c7:00.0] = AMD Radeon 780M Graphics
 *     amdGpuClock[0000:c7:00.0] = 2700000000
 *
 * A card that does not publish the reading has no cell at all rather than a
 * placeholder, so its row is passed over here.
 *
 * Stored unscaled: the frequency sensor class is labelled in Hz.
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

    // amdGpuClock, the fifth column of amdGpuEntry. Numbered here rather than
    // translated, because a stored sensor OID is numeric and this MIB is not
    // in the search path of a UNIX device.
    $amdgpu_clock = '.1.3.6.1.4.1.60652.101.1.1.5';

    foreach ($amdgpu_table as $amdgpu_pci => $amdgpu_card) {
        $amdgpu_value = $amdgpu_card['amdGpuClock'] ?? '';

        if (! is_numeric($amdgpu_value)) {
            continue;
        }

        // The address stays in the group even when a name is known, so
        // two identical cards in one host remain distinguishable. Kept in
        // step with sensors/power/unix.inc.php: should the two name a group
        // differently, one card would land in two groups under Health.
        $amdgpu_product = $amdgpu_card['amdGpuProductName'] ?? '';
        $amdgpu_group = $amdgpu_product === ''
            ? 'GPU ' . $amdgpu_pci
            : $amdgpu_product . ' (' . $amdgpu_pci . ')';

        // The walk hands the address back readable; an OID carries it as
        // its length followed by one sub-identifier per character.
        $amdgpu_oid = $amdgpu_clock . '.' . Oid::encodeString($amdgpu_pci);

        discover_sensor(
            null,
            'frequency',
            $device,
            $amdgpu_oid,
            $amdgpu_pci . '.amdGpuClock',
            'amdgpu',
            'Clock',
            1,
            1,
            null,
            null,
            null,
            null,
            $amdgpu_value,
            'snmp',
            null,
            null,
            null,
            $amdgpu_group
        );
    }
}
