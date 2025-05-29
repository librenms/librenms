<?php
/**
 * vrp.inc.php
 *
 * Polling for Huawei VRP OS
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
 * @copyright  2024 LibreNMS
 * @author     Daniel 'f0o' Preussker <git@f0o.dev>
 */
use Log;

foreach (SnmpQuery::hideMib()->walk('HUAWEI-L2IF-MIB::hwL2IfPortIfIndex')->table(1) as $vrp_k => $vrp_port) {
    $vrp_ifIndex = $vrp_port['hwL2IfPortIfIndex'];
    $vrp_pvid = SnmpQuery::hideMib()->get('HUAWEI-L2IF-MIB::hwL2IfPVID.' . $vrp_k)->value();
    if ($vrp_pvid > 0) {
        Log::debug('Huawei VRP Port: ' . $vrp_ifIndex . ' VLAN: ' . $vrp_pvid);
        $port_stats[$vrp_ifIndex]['dot1qPvid'] = $vrp_pvid;
    }
}

unset($vrp_ports, $vrp_port, $vrp_ifIndex, $vrp_pvid, $vrp_k);
