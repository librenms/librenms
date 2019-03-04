<?php
/**
* infinera-groove.inc.php
*
* LibreNMS ports poller module for Infinera Groove
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
* @copyright  2019 Nick Hilliard
* @author     Nick Hilliard <nick@foobar.org>
*/


echo 'Port types:';

foreach (array ('100', '40', '10') as $infineratype) {
    if (!is_array($cg_stats)) {
        echo ' eth'.$infineratype.'g';
        $cg_stats = snmpwalk_cache_multi_oid($device, 'eth'.$infineratype.'gEntry', $cg_stats, 'CORIANT-GROOVE-MIB');
        $cg_stats = snmpwalk_cache_multi_oid($device, 'eth'.$infineratype.'gStatistics', $cg_stats, 'CORIANT-GROOVE-MIB');
    }

    $required = array(
        'ifAlias'               => 'eth'.$infineratype.'gAliasName',
        'ifAdminStatus'         => 'eth'.$infineratype.'gAdminStatus',
        'ifOperStatus'          => 'eth'.$infineratype.'gOperStatus',
        'ifType'                => 'Ethernet',
        'ifHCInBroadcastPkts'   => 'eth'.$infineratype.'gStatisticsEntryInBroadcastPackets',
        'ifHCInMulticastPkts'   => 'eth'.$infineratype.'gStatisticsEntryInMulticastPackets',
        'ifHCInOctets'          => 'eth'.$infineratype.'gStatisticsEntryInOctets',
        'ifHCInUcastPkts'       => 'eth'.$infineratype.'gStatisticsEntryInPackets',
        'ifHCOutBroadcastPkts'  => 'eth'.$infineratype.'gStatisticsEntryOutBroadcastPackets',
        'ifHCOutMulticastPkts'  => 'eth'.$infineratype.'gStatisticsEntryOutMulticastPackets',
        'ifHCOutOctets'         => 'eth'.$infineratype.'gStatisticsEntryOutOctets',
        'ifHCOutUcastPkts'      => 'eth'.$infineratype.'gStatisticsEntryOutPackets',
        'ifHighSpeed'           => $infineratype*1000,
    );

    foreach ($cg_stats as $index => $tmp_stats) {
        $indexids = explode('.', $index);

        if (!isset($cg_stats[$index]['eth'.$infineratype.'gAdminStatus'])) {
            continue;
        }

        // 100g ports use shelfId, slotId, portId
        // 40g and 10g ports use shelfId, slotId, portId, subportId
        $descr = $infineratype.'gbe-'.$indexids[0].'/'.$indexids[1].'/'.$indexids[3];
        if ($infineratype != 100) {
            $descr .= '/'.$indexids[4];
        }

        // librenms expects the index to be bigint(20) => we grab 3 decimal
        // spaces per indexid to make a numeric ifindex.  This is hacky.
        $lindex = '';
        for ($i = 0; $i <= 4; $i++) {
            $lindex .= sprintf('%03d', $indexids[$i]);
        }

        // convert to integer
        $lindex = $lindex + 0;

        $port_stats[$lindex]['ifName'] = $descr;
        $port_stats[$lindex]['ifDescr'] = $descr;

        foreach ($required as $normaloid => $infineraoid) {
            // this is a bit hacky
            if (preg_match('/^eth/', $required[$normaloid])) {
                $port_stats[$lindex][$normaloid] = $cg_stats[$index][$infineraoid];
            } else {
                $port_stats[$lindex][$normaloid] = $required[$normaloid];
            }
        }
    }
}
