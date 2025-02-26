<?php
/**
 * fortiswitch.inc.php
 *
 * Fortinet has special needs:
 *
 * BRIDGE-MIB::dot1dTpFdbAddress.142 = STRING: 58:1c:f8:62:f5:2a
 * BRIDGE-MIB::dot1dTpFdbPort.142 = INTEGER: 32769
 * BRIDGE-MIB::dot1dBasePort.29 = INTEGER: 32769
 *
 * MAC on index 142 is on fdbPort 32769 which translates to physical port 29
 *
 * Fortinet doesn't give us the VLAN ID. Bug report in 9239914 & 10430993
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
 * @copyright  2025 CTNET BV
 * @author     Rudy Broersma <r.broersma@ctnet.nl>
 */

use Illuminate\Support\Facades\Log;
use LibreNMS\Util\Mac;

$macTable = SnmpQuery::hideMib()->walk('BRIDGE-MIB::dot1dTpFdbAddress')->table();
$portTable = SnmpQuery::hideMib()->walk('BRIDGE-MIB::dot1dTpFdbPort')->table();
$basePortTable = SnmpQuery::hideMib()->walk('BRIDGE-MIB::dot1dBasePort')->table();
$basePortIfIndexTable = SnmpQuery::hideMib()->walk('BRIDGE-MIB::dot1dBasePortIfIndex')->table();

foreach ($macTable['dot1dTpFdbAddress'] as $dot1dTpFdbPort => $mac) {
    $fdbPort = $portTable['dot1dTpFdbPort'][$dot1dTpFdbPort];
    $dot1dBasePort = array_search($fdbPort, $basePortTable['dot1dBasePort']);
    $dot1dBasePortIfIndex = $basePortIfIndexTable['dot1dBasePortIfIndex'][$dot1dBasePort];

    $port_id = PortCache::getIdFromIfIndex($dot1dBasePortIfIndex);
    $vlan_id = 0; // Bug 9239914

    $mac_address = Mac::parse($mac)->hex(); // pad zeros and remove colons

    if ($port_id == null) {
        Log::debug("No port known for $mac\n");
        continue;
    }

    if (strlen($mac_address) != 12) {
        Log::debug("MAC address padding failed for $mac\n");
        continue;
    }

    $insert[$vlan_id][$mac_address]['port_id'] = $port_id;
    Log::debug("vlan $vlan_id mac $mac_address port ($dot1dBasePort) $port_id\n");
}

echo PHP_EOL;
