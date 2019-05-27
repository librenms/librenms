<?php
/**
 * mpls.inc.php
 *
 * Polling MPLS LSPs
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
 * @copyright  20169 Vitali Kari
 * @author     Vitali Kari <vitali.kari@gmail.com>
 */
use LibreNMS\Config;

echo "\nMPLS LSPs: ";
if (Config::get('enable_mpls')) {
    $lsps = dbFetchRows('SELECT * FROM `mpls_lsps` WHERE `device_id` = ?', [$device['device_id']]);
    $paths = dbFetchRows('SELECT * FROM `mpls_lsp_paths` WHERE `device_id` = ?', [$device['device_id']]);

    if ($device['os'] == 'timos') {
        $mplsLspCache = snmpwalk_cache_multi_oid($device, 'vRtrMplsLspTable', [], 'TIMETRA-MPLS-MIB', 'nokia');
        $mplsLspCache = snmpwalk_cache_multi_oid($device, 'vRtrMplsLspLastChange', $mplsLspCache, 'TIMETRA-MPLS-MIB', 'nokia', '-OQUst');
        $mplsLspCache = snmpwalk_cache_multi_oid($device, 'vRtrMplsLspStatTable', $mplsLspCache, 'TIMETRA-MPLS-MIB', 'nokia');
        foreach ($mplsLspCache as $key => $value) {
            $oids = explode('.', $key);
            $lsp = [
                'vrf_oid' => $oids[0],
                'lsp_oid' => $oids[1],
                'device_id' => $device['device_id'],
                'mplsLspRowStatus' => $value['vRtrMplsLspRowStatus'],
                'mplsLspLastChange' => round($value['vRtrMplsLspLastChange'] / 100),
                'mplsLspName' => $value['vRtrMplsLspName'],
                'mplsLspAdminState' => $value['vRtrMplsLspAdminState'],
                'mplsLspOperState' => $value['vRtrMplsLspOperState'],
                'mplsLspFromAddr' => $value['vRtrMplsLspFromAddr'],
                'mplsLspToAddr' => $value['vRtrMplsLspToAddr'],
                'mplsLspType' => $value['vRtrMplsLspType'],
                'mplsLspFastReroute' => $value['vRtrMplsLspFastReroute'],
                'mplsLspAge' => $value['vRtrMplsLspAge'],
                'mplsLspTimeUp' => $value['vRtrMplsLspTimeUp'],
                'mplsLspTimeDown' => $value['vRtrMplsLspTimeDown'],
                'mplsLspPrimaryTimeUp' => $value['vRtrMplsLspPrimaryTimeUp'],
                'mplsLspTransitions' => $value['vRtrMplsLspTransitions'],
                'mplsLspLastTransition' => round($value['vRtrMplsLspLastTransition'] / 100),
                'mplsLspConfiguredPaths' => $value['vRtrMplsLspConfiguredPaths'],
                'mplsLspStandbyPaths' => $value['vRtrMplsLspStandbyPaths'],
                'mplsLspOperationalPaths'  => $value['vRtrMplsLspOperationalPaths'],
            ];
            dbUpdate($lsp, 'mpls_lsps', 'device_id = ? AND vrf_oid = ? AND lsp_oid = ?', [$device['device_id'], $oids[0], $oids[1]]);
            echo ".";
        }
        echo "\nMPLS LSP Paths: ";
        $mplsPathCache = snmpwalk_cache_multi_oid($device, 'vRtrMplsLspPathTable', [], 'TIMETRA-MPLS-MIB', 'nokia');
        $mplsPathCache = snmpwalk_cache_multi_oid($device, 'vRtrMplsLspPathLastChange', $mplsPathCache, 'TIMETRA-MPLS-MIB', 'nokia', '-OQUst');
        $mplsPathCache = snmpwalk_cache_multi_oid($device, 'vRtrMplsLspPathStatTable', $mplsPathCache, 'TIMETRA-MPLS-MIB', 'nokia');
        foreach ($mplsPathCache as $key => $value) {
            $oids = explode('.', $key);
            $lsp_id = dbFetchCell('SELECT lsp_id from `mpls_lsps` WHERE device_id = ? AND vrf_oid = ? AND lsp_oid = ?', [$device['device_id'], $oids[0], $oids[1]]);
            $path = [
                'lsp_id' => $lsp_id,
                'path_oid' => $oids[2],
                'device_id' => $device['device_id'],
                'mplsLspPathRowStatus' => $value['vRtrMplsLspPathRowStatus'],
                'mplsLspPathLastChange' => round($value['vRtrMplsLspPathLastChange'] / 100),
                'mplsLspPathType' => $value['vRtrMplsLspPathType'],
                'mplsLspPathBandwidth' => $value['vRtrMplsLspPathBandwidth'],
                'mplsLspPathOperBandwidth' => $value['vRtrMplsLspPathOperBandwidth'],
                'mplsLspPathAdminState' => $value['vRtrMplsLspPathAdminState'],
                'mplsLspPathOperState' => $value['vRtrMplsLspPathOperState'],
                'mplsLspPathState' => $value['vRtrMplsLspPathState'],
                'mplsLspPathFailCode' => $value['vRtrMplsLspPathFailCode'],
                'mplsLspPathFailNodeAddr' => $value['vRtrMplsLspPathFailNodeAddr'],
                'mplsLspPathMetric' => $value['vRtrMplsLspPathMetric'],
                'mplsLspPathOperMetric' => $value['vRtrMplsLspPathOperMetric'],
                'mplsLspPathTimeUp' => $value['vRtrMplsLspPathTimeUp'],
                'mplsLspPathTimeDown' => $value['vRtrMplsLspPathTimeDown'],
                'mplsLspPathTransitionCount' => $value['vRtrMplsLspPathTransitionCount'],
            ];
            dbUpdate($path, 'mpls_lsp_paths', 'device_id = ? AND lsp_id = ? AND path_oid = ?', [$device['device_id'], $lsp_id, $oids[2]]);
            echo ".";
        }
        unset($mplsLspCache, $mplsPathCache);
    }
    unset($lsps, $paths);
}
