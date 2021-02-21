<?php
/**
 * ict-pdu.inc.php
 *
 * LibreNMS status sensor discovery module for ICT DC Distribution Panel
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
 * @copyright  2017 Lorenzo Zafra
 * @author     Lorenzo Zafra<zafra@ualberta.ca>
 */
$oids = snmpwalk_cache_oid($device, 'outputEntry', [], 'ICT-DISTRIBUTION-PANEL-MIB');

if (is_array($oids)) {
    $state_name = 'outputFuseStatus';
    $states = [
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'OK'],
        ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'OPEN'],
    ];
    create_state_index($state_name, $states);

    foreach ($oids as $index => $entry) {
        $fuse_state_oid = '.1.3.6.1.4.1.39145.10.8.1.4.' . $index;
        $fuse_number = (int) $index + 1;
        $descr = 'Fuse #' . $fuse_number;

        $current_value_string = $entry[$state_name];
        if ($current_value_string == 'OK') {
            $current_value = 1;
        } elseif ($current_value_string == 'OPEN') {
            $current_value = 2;
        }

        discover_sensor($valid['sensor'], 'state', $device, $fuse_state_oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $current_value, 'snmp', $index);

        create_sensor_to_state_index($device, $state_name, $index);
    }
}
