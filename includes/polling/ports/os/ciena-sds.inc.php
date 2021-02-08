<?php
/**
 * ciena-sds.inc.php
 *
 * LibreNMS Ciena port poller include
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
 * @copyright  2020 Dan Baker, Lancaster-Lebanon IU13
 */

// These hardware platforms either have a broken IF-MIB or have excessively long caching of values
$bad_HC_counter_devices = [
    '170-5170-905',
    '154-8700-930',
];

if (in_array($device['hardware'], $bad_HC_counter_devices)) {
    $ciena_pm = snmpwalk_cache_oid($device, 'cienaCesPmExtendedTxRxStatisticsBinEntry', [], 'CIENA-CES-PM');

    d_echo($ciena_pm);

    foreach ($ciena_pm as $index => $ciena_pm_entry) {
        // PM interface indices are different than regular interface indices.
        // PM index for port 1/1 -- 1000001
        // if index for port 1/1 --  100001

        $nms_index = $index - 900000;

        if (isset($port_stats[$nms_index])) {
            $port_stats[$nms_index]['ifHCInOctets'] = $ciena_pm_entry['cienaCesPmExtendedTxRxStatsBinRxBytes'];
            $port_stats[$nms_index]['ifHCInUcastPkts'] = $ciena_pm_entry['cienaCesPmExtendedTxRxStatsBinRxPkts'];
            $port_stats[$nms_index]['ifHCInMulticastPkts'] = $ciena_pm_entry['cienaCesPmExtendedTxRxStatsBinRxMcastPkts'];
            $port_stats[$nms_index]['ifHCInBroadcastPkts'] = $ciena_pm_entry['cienaCesPmExtendedTxRxStatsBinRxBcastPkts'];
            $port_stats[$nms_index]['ifHCOutOctets'] = $ciena_pm_entry['cienaCesPmExtendedTxRxStatsBinTxBytes'];
            $port_stats[$nms_index]['ifHCOutUcastPkts'] = $ciena_pm_entry['cienaCesPmExtendedTxRxStatsBinTxPkts'];
            $port_stats[$nms_index]['ifHCOutMulticastPkts'] = $ciena_pm_entry['cienaCesPmExtendedTxRxStatsBinTxMcastPkts'];
            $port_stats[$nms_index]['ifHCOutBroadcastPkts'] = $ciena_pm_entry['cienaCesPmExtendedTxRxStatsBinTxBcastPkts'];
            $port_stats[$nms_index]['ifInErrors'] = $ciena_pm_entry['cienaCesPmExtendedTxRxStatsBinRxCrcErrorPkts'];
            $port_stats[$nms_index]['ifOutErrors'] = $ciena_pm_entry['cienaCesPmExtendedTxRxStatsBinTxCrcErrorPkts'];
            $port_stats[$nms_index]['ifInDiscards'] = $ciena_pm_entry['cienaCesPmExtendedTxRxStatsBinInDiscards'];
        }
    }
}
