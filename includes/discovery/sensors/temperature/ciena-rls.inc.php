<?php
/**
 * ciena-rls.inc.php
 *
 * -Description-
 *
 * Discover temperature sensors from the amplifiers of the Ciena
 * Reconfigurable Line System (RLS) and display location in chassis.
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
 * Traps when Adva objects are created. This includes Remote User Login object,
 * Flow Creation object, and LAG Creation object.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2024 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */
$oids = snmp_walk($device, 'rlsCircuitPackCurrTemprature', '-OsqnU', 'CIENA-6500R-INVENTORY-MIB');
if (isset($oids) && $oids) {
    d_echo($oids . "\n");
    $oids = trim($oids);
    $type = 'ciena-rls';
    foreach (explode(PHP_EOL, $oids) as $data) {
        [$oid, $value] = explode(' ', $data);
        $index = substr($oid, 35);
        $expIndex = explode('.', $index);
        $slotId = '';
        $slotLen = array_shift($expIndex);
        while ($slotLen > 0) {
            $slotId .= chr(array_shift($expIndex));
            $slotLen--;
        }
        $slotName = snmp_get($device, 'rlsCircuitPackCtype.' . $index, '-OqvU', 'CIENA-6500R-INVENTORY-MIB');
        $descr = "$slotName Slot $slotId";
        $group = null;
        $low_limit = null;
        $low_warn_limit = null;
        discover_sensor(
            $valid['sensor'],
            'temperature',
            $device,
            $oid,
            'rlsCircuitPackCurrTemprature.' . $index,
            $type,
            $descr,
            1,
            1,
            $low_limit,
            $low_warn_limit,
            null,
            null,
            $value,
            'snmp',
            $entPhysicalIndex,
            $entPhysicalIndex_measured,
            null,
            $group
        );
    }
}
