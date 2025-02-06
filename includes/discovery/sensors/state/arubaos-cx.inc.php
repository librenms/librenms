<?php
/**
 * arubaos-cx.inc.php
 *
 * LibreNMS state sensor and translation/discovery module for ArubaOS-CX Switches
 *
 * ArubaOS-CX switches return certain operational status values as strings,
 * such as the VSF operational and member status. LibreNMS expects numeric
 * values.
 *
 * This discovery modules translates the string status representation to a
 * numerical value, so that LibreNMS can properly store and display it's value
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
 *
 * @copyright  2024 CTNET BV
 * @author     Rudy Broersma <tozz@kijkt.tv>
 */
$vsfOpStatusStates = [
    ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'No Split'],
    ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'Fragment Active'],
    ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'Fragment Inactive'],
];

$vsfMemberTableStates = [
    ['value' => 10, 'generic' => 2, 'graph' => 0, 'descr' => 'Not Present'],
    ['value' => 11, 'generic' => 1, 'graph' => 0, 'descr' => 'Booting'],
    ['value' => 12, 'generic' => 0, 'graph' => 0, 'descr' => 'Ready'],
    ['value' => 13, 'generic' => 1, 'graph' => 0, 'descr' => 'Version Mismatch'],
    ['value' => 14, 'generic' => 2, 'graph' => 0, 'descr' => 'Communication Failure'],
    ['value' => 15, 'generic' => 2, 'graph' => 0, 'descr' => 'In Other Fragment'],
];

$stateLookupTable = [
    // arubaWiredVsfv2OperStatus
    'no_split' => 0,
    'fragment_active' => 1,
    'fragment_inactive' => 2,

    //arubaWiredVsfv2MemberTable
    'not_present' => 10,
    'booting' => 11,
    'ready' => 12,
    'version_mismatch' => 13,
    'communication_failure' => 14,
    'in_other_fragment' => 15,
];

$temp = snmpwalk_cache_multi_oid($device, 'arubaWiredVsfv2OperStatus', [], 'ARUBAWIRED-VSFv2-MIB');
if (is_array($temp)) {
    echo 'ArubaOS-CX VSF Operational Status: ';
    //Create State Index
    $state_name = 'arubaWiredVsfv2OperStatus';
    create_state_index($state_name, $vsfOpStatusStates);

    foreach ($temp as $index => $data) {
        $sensor_value = $stateLookupTable[$data['arubaWiredVsfv2OperStatus']];

        $descr = 'VSF Status';
        $oid = '.1.3.6.1.4.1.47196.4.1.1.3.15.1.1.1.' . $index;
        discover_sensor(null, 'state', $device, $oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $sensor_value, 'snmp', null, null, null, 'VSF');
    }
}

$temp = snmpwalk_cache_multi_oid($device, 'arubaWiredVsfv2MemberTable', [], 'ARUBAWIRED-VSFv2-MIB');
if (is_array($temp)) {
    echo 'ArubaOS-CX VSF Member Status: ';
    //Create State Index
    $state_name = 'arubaWiredVsfv2MemberTable';
    create_state_index($state_name, $vsfMemberTableStates);
    foreach ($temp as $index => $data) {
        $sensor_value = $stateLookupTable[$data['arubaWiredVsfv2MemberStatus']];

        $descr = 'Member ' . $data['arubaWiredVsfv2MemberSerialNum'] . ' Status';
        $oid = '.1.3.6.1.4.1.47196.4.1.1.3.15.1.2.1.3.' . $index;
        discover_sensor(null, 'state', $device, $oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $sensor_value, 'snmp', null, null, null, 'VSF');
    }
}
