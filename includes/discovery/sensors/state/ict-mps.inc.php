<?php
/**
 * ict-mps.inc.php
 *
 * LibreNMS status sensor discovery module for ICT Modular Power System
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2017 Lorenzo Zafra
 * @author     Lorenzo Zafra<zafra@ualberta.ca>
 */

$oids = snmpwalk_cache_oid($device, 'moduleTable', [], 'ICT-MODULAR-POWER-SYSTEM-MIB');

if (is_array($oids)) {
    ## Module Status
    $state_name = 'moduleStatus';
    $states = [
        ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'notInstalled'],
        ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'ok'],
        ['value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'alarm'],
    ];
    create_state_index($state_name, $states);
    foreach ($oids as $index => $entry) {
        $module_status_oid = '.1.3.6.1.4.1.39145.13.10.1.2.' . $index;
        $descr = "Module #$index Status";
        $current_value = $entry[$state_name];

        discover_sensor($valid['sensor'], 'state', $device, $module_status_oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $current_value, 'snmp', $index);
        create_sensor_to_state_index($device, $state_name, $index);
    }
    ## Module Type
    $state_name = 'moduleType';
    $states = [
        ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'notInstalled'],
        ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'power'],
        ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'battery'],
        ['value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'distribution'],
    ];
    create_state_index($state_name, $states);
    foreach ($oids as $index => $entry) {
        $module_status_oid = '.1.3.6.1.4.1.39145.13.10.1.3.' . $index;
        $descr = "Module #$index Type";
        $current_value = $entry[$state_name];

        discover_sensor($valid['sensor'], 'state', $device, $module_status_oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $current_value, 'snmp', $index);
        create_sensor_to_state_index($device, $state_name, $index);
    }
}
