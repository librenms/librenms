<?php
/**
 * airos-af-ltu.inc.php
 *
 * LibreNMS ports poller module for Ubiquiti airFiber 5XHD
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
 * @copyright  2020 Denny Friebe
 * @author     Denny Friebe <denny.friebe@icera-network.de>
 */

$airos_stats = snmpwalk_cache_oid($device, '.1.3.6.1.4.1.41112.1.10.1.6', array(), 'UBNT-AFLTU-MIB');

if (isset($airos_stats[0]['afLTUethConnected'])) {
    foreach ($port_stats as $index => $afport_stats) {
        if ($afport_stats['ifDescr'] == 'eth0') {
            $port_stats[$index]['ifOperStatus'] = ($airos_stats[0]['afLTUethConnected'] == "connected" ? "up" : "down");
            $port_stats[$index]['ifHCInOctets'] = $airos_stats[0]['afLTUethRxBytes'];
            $port_stats[$index]['ifHCOutOctets'] = $airos_stats[0]['afLTUethTxBytes'];
            $port_stats[$index]['ifHCInUcastPkts'] = $airos_stats[0]['afLTUethRxPps'];
            $port_stats[$index]['ifHCOutUcastPkts'] = $airos_stats[0]['afLTUethTxPps'];
            $port_stats[$index]['ifHighSpeed'] = '1000';

            break;
        }
    }
}

unset($airos_stats);
