<?php
/**
 * ict-swi.inc.php
 *
 * LibreNMS status sensor discovery module for ICT Sine Wave Inverter
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

$inverterStatus = (int)(snmpget($device, 'inverterStatus.0', '-Oqv', 'ICT-SINE-WAVE-INVERTER-MIB'));
if ($inverterStatus >= 0) {
    ## Inverter Status
    $index = 0;
    $state_name = 'inverterStatus';
    $states = [
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'enabled'],
        ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'disabled'],
    ];
    create_state_index($state_name, $states);
    $oid = '.1.3.6.1.4.1.39145.12.9.' . $index;
    $descr = "Inverter Status";
    $current_value = $inverterStatus;

    discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $current_value, 'snmp', $index);
    create_sensor_to_state_index($device, $state_name, $index);
}

$inverterControl = (int)(snmpget($device, 'inverterControl.0', '-Oqv', 'ICT-SINE-WAVE-INVERTER-MIB'));
if ($inverterControl >= 0) {
    ## Inverter Control
    $index = 0;
    $state_name = 'inverterControl';
    $states = [
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'enabled'],
        ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'disabled'],
    ];
    create_state_index($state_name, $states);
    $oid = '.1.3.6.1.4.1.39145.12.10.' . $index;
    $descr = "Inverter Control";
    $current_value = $inverterControl;

    discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $current_value, 'snmp', $index);
    create_sensor_to_state_index($device, $state_name, $index);
}


$transferRelayStatus = (int)(snmpget($device, 'transferRelayStatus.0', '-Oqv', 'ICT-SINE-WAVE-INVERTER-MIB'));
if ($transferRelayStatus >= 0) {
    ## Transfer Relay Status
    $index = 0;
    $state_name = 'transferRelayStatus';
    $states = [
        ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'inverter'],
        ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'grid'],
    ];
    create_state_index($state_name, $states);
    $oid = '.1.3.6.1.4.1.39145.12.11.' . $index;
    $descr = "Transfer Relay Status";
    $current_value = $transferRelayStatus;

    discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $current_value, 'snmp', $index);
    create_sensor_to_state_index($device, $state_name, $index);
}
