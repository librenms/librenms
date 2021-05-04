<?php
/**
 * vrp.inc.php
 *
 * LibreNMS sensors state discovery module for HP Procurve
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
 * @copyright  2019 PipoCanaja
 * @author     PipoCanaja
 */
$stacked_device = empty($pre_cache['hwStackMemberInfoTable']) ? false : count($pre_cache['hwStackMemberInfoTable']) > 1;
// If we have more than 1 device in the stack, then we should alert on stack ports not up

if ($stacked_device) {
    $state_name = 'hwStackPortStatus';
    $states = [
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Up'],
        ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'Down'],
    ];
} else {
    $state_name = 'hwStackPortStatusNotStacked';
    $states = [
        ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'Up'],
        ['value' => 2, 'generic' => 3, 'graph' => 0, 'descr' => 'Down'],
    ];
}

foreach ($pre_cache['hwStackPortTable'] as $index => $data) {
    $subindex = explode('.', $index);
    $state_oid = '.1.3.6.1.4.1.2011.5.25.183.1.21.1.5.' . $index;
    $state_descr = 'Unit ' . $subindex[0] . ' stack-port ' . $subindex[1] . ' Status';
    $state = $data['hwStackPortStatus'];
    $state_index = $index;

    create_state_index($state_name, $states);

    discover_sensor($valid['sensor'], 'state', $device, $state_oid, $state_index, $state_name, $state_descr, '1', '1', null, null, null, null, $state, 'snmp', null, null, null, 'Stack');
    create_sensor_to_state_index($device, $state_name, $state_index);
}
