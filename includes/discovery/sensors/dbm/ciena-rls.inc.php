<?php
/**
 * ciena-rls.inc.php
 *
 * -Description-
 *
 * Discover power levels from the amplifiers of the Ciena Reconfigurable
 * Line System (RLS).
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
$oidList = ['rlsInventoryAmpsInCurrPower', 'rlsInventoryAmpsInMinPower', 'rlsInventoryAmpsInMaxPower', 'rlsInventoryAmpsOutCurrPower', 'rlsInventoryAmpsOutMinPower', 'rlsInventoryAmpsOutMaxPower', 'rlsInventoryAmpsOpticalReturnLoss'];

foreach ($oidList as $oidName) {
    $oids = snmp_walk($device, $oidName, '-OsqnU', 'CIENA-6500R-INVENTORY-AMPS-MIB');
    if (isset($oids) && $oids) {
        d_echo($oids . "\n");
        $oids = trim($oids);
        $type = 'ciena-rls';
        foreach (explode(PHP_EOL, $oids) as $data) {
            [$oid, $value] = explode(' ', $data);
            $index = substr($oid, 37);
            //Index is a tuple byte string of SlotID and AmpID.
            $expIndex = explode('.', $index);
            $slotId = '';
            $ampId = '';
            $slotLen = array_shift($expIndex);
            while ($slotLen > 0) {
                $slotId .= chr(array_shift($expIndex));
                $slotLen--;
            }
            $ampLen = array_shift($expIndex);
            while ($ampLen > 0) {
                $ampId .= chr(array_shift($expIndex));
                $ampLen--;
            }
            $descr = "Slot $slotId $ampId";

            $group = null;
            $low_limit = null;
            $low_warn_limit = null;
            if ($oidName == 'rlsInventoryAmpsInCurrPower') {
                $group = 'Amplifier Input - Current';
                $low_limit = snmp_walk($device, 'rlsInventoryAmpsInputLosThreshold.' . $index, '-OqvU', 'CIENA-6500R-INVENTORY-AMPS-MIB');
            } elseif ($oidName == 'rlsInventoryAmpsInMinPower') {
                $group = 'Amplifier Input - Minimum';
            } elseif ($oidName == 'rlsInventoryAmpsInMaxPower') {
                $group = 'Amplifier Input - Maximum';
            } elseif ($oidName == 'rlsInventoryAmpsOutCurrPower') {
                $group = 'Amplifier Output - Current';
            } elseif ($oidName == 'rlsInventoryAmpsOutMinPower') {
                $group = 'Amplifier Output - Minimum';
            } elseif ($oidName == 'rlsInventoryAmpsOutMaxPower') {
                $group = 'Amplifier Output - Maximum';
            } elseif ($oidName == 'rlsInventoryAmpsOpticalReturnLoss') {
                if (strpos($descr, 'Pre-Amp')) { //No ORL on Pre-Amps
                    break;
                }
                $group = 'Amplifier Optical Return Loss';
                $low_warn_limit = snmp_walk($device, 'rlsInventoryAmpsOrlThreshold.' . $index, '-OqvU', 'CIENA-6500R-INVENTORY-AMPS-MIB'); //RLS ORL alarm level
                $low_limit = snmp_walk($device, 'rlsInventoryAmpsAprThreshold.' . $index, '-OqvU', 'CIENA-6500R-INVENTORY-AMPS-MIB'); //RLS ORL level when APR becomes active
            }

            discover_sensor(
                $valid['sensor'],
                'dbm',
                $device,
                $oid,
                $oidName . '.' . $index,
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
}
