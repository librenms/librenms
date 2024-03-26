<?php
/**
 * ciena-rls.inc.php
 *
 * -Description-
 *
 * State sensors for the Ciena Reconfigurable Line System.
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

$ampModes = [
    ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Power'],
    ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'Gain'],
    ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'GainClamp'],
];

$ampStates = [
    ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'Unknown'],
    ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'Off'],
    ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'APR'],
    ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'Normal'],
    ['value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'Clamped'],
];

$ampGainModes = [
    ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Low'],
    ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'High'],
];

$ampForceShutdown = [
    ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Disabled'],
    ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'Enabled'],
];

$oidList = array('rlsInventoryAmpsAmpMode', 'rlsInventoryAmpsState', 'rlsInventoryAmpsGainMode', 'rlsInventoryAmpsForcedShutdown');
foreach ($oidList as $oidName) {

    $oids = snmp_walk($device, $oidName, '-OesqnU', 'CIENA-6500R-INVENTORY-AMPS-MIB');
    if (isset($oids) && $oids) {
        d_echo($oids . "\n");
        $oids = trim($oids);

        $group = null;
        if ($oidName == 'rlsInventoryAmpsAmpMode') {
            $group = 'Amplifier Mode';
            create_state_index($oidName, $ampModes);
        } elseif ($oidName == 'rlsInventoryAmpsState') {
            $group = 'Amplifier State';
            create_state_index($oidName, $ampStates);
        } elseif ($oidName == 'rlsInventoryAmpsGainMode') {
            $group = 'Amplifier Gain Mode';
            create_state_index($oidName, $ampGainModes);
        } elseif ($oidName == 'rlsInventoryAmpsForcedShutdown') {
            $group = 'Amplifier Forced Shutdown State';
            create_state_index($oidName, $ampForceShutdown);
        }

        foreach (explode(PHP_EOL, $oids) as $data) {
            [$oid, $value] = explode(' ', $data);
            $index = substr($oid, 36);
            //Index is a tuple byte string of SlotID and AmpID.
            $expIndex = explode('.', $index);
            $slotId = '';
            $ampId = '';
            $slotLen = array_shift($expIndex);
            while ($slotLen > 0) {
                $slotId .= chr(array_shift($expIndex));
                --$slotLen;
            }
            $ampLen = array_shift($expIndex);
            while ($ampLen > 0) {
                $ampId .= chr(array_shift($expIndex));
                --$ampLen;
            }
            $descr = "Slot $slotId $ampId";
            $stateIndex = $oidName . "." . $index;
            discover_sensor(
                $valid['sensor'],
                'state',
                $device,
                $oid,
                $stateIndex,
                $oidName,
                $descr,
                1,
                1,
                null,
                null,
                null,
                null,
                $value,
                'snmp',
                $entPhysicalIndex,
                $entPhysicalIndex_measured,
                null,
                $group
            );
            create_sensor_to_state_index($device, $oidName, $stateIndex);
        }
    }
}
