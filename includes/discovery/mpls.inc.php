<?php
/**
 * mpls.inc.php
 *
 * Discover MPLS LSPs
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
    if ($device['os'] == 'timos') {
        $mplsLspCache = snmpwalk_cache_multi_oid($device, 'vRtrMplsLspTable', [], 'TIMETRA-MPLS-MIB', 'nokia');
        $mplsLspCache = snmpwalk_cache_multi_oid($device, 'vRtrMplsLspLastChange', $mplsLspCache, 'TIMETRA-MPLS-MIB', 'nokia', '-OQUst');
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
            ];
            if (dbFetchCell('SELECT COUNT(*) from `mpls_lsps` WHERE device_id = ? AND vrf_oid = ? AND lsp_oid = ?', [$device['device_id'], $oids[0], $oids[1]]) < '1') {
                dbInsert($lsp, 'mpls_lsps');
                echo "+";
            } else {
                dbUpdate($lsp, 'mpls_lsps', 'device_id = ? AND vrf_oid = ? AND lsp_oid = ?', [$device['device_id'], $oids[0], $oids[1]]);
                echo ".";
            }
        }
        // mark valid lsps
        $lsps = dbFetchRows('SELECT `vrf_oid`, `lsp_oid` FROM `mpls_lsps` WHERE `device_id` = ?', [$device['device_id']]);
        foreach ($lsps as $key_db => $value_db) {
            foreach ($mplsLspCache as $key => $value) {
                $oids = explode('.', $key);
                if ($oids[0] == $value_db['vrf_oid'] and $oids[1] == $value_db['lsp_oid']) {
                    $lsps[$key_db]['valid'] = 'true';
                }
            }
        }
        // delete stale lsps
        foreach ($lsps as $value) {
            if (!isset($value['valid'])) {
                dbDelete('mpls_lsps', 'device_id = ? AND vrf_oid = ? AND lsp_oid = ?', [$device['device_id'], $value['vrf_oid'], $value['lsp_oid']]);
                echo "-";
            }
        }
        unset($mplsLspCache, $lsps);
    }
}
