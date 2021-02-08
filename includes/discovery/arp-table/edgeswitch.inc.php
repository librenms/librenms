<?php
/**
 * edgeswitch.inc.php
 *
 * Arp Table discovery file for EdgeSwitch
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */
$binding = snmpwalk_group($device, 'agentDynamicDsBindingTable', 'EdgeSwitch-SWITCHING-MIB', 1);

foreach ($binding as $mac => $data) {
    $arp_data[$data['agentDynamicDsBindingIfIndex']]['ipNetToMediaPhysAddress'][$data['agentDynamicDsBindingIpAddr']] = $data['agentDynamicDsBindingMacAddr'];
    $arp_data[$data['agentDynamicDsBindingIfIndex']]['ipNetToPhysicalPhysAddress']['ipv4'][$data['agentDynamicDsBindingIpAddr']] = $data['agentDynamicDsBindingMacAddr'];
}
