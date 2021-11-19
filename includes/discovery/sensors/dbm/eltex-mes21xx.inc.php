<?php
/*
 * LibreNMS discovery module for Eltex-MES21xx SFP Dbm
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 *
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

$low_limit = $low_warn_limit = -15;
$high_warn_limit = $high_limit = 0;
$divisor = 1000;

$oids = $pre_cache['eltex-mes21xx_rlPhyTestGetResult'];
if ($oids) {
    d_echo('Eltex-MES SFP dBm');
    foreach (explode("\n", $oids) as $data) {
        if ($data) {
            $split = trim(explode(' ', $data)[0]);
            $value = trim(explode(' ', $data)[1]);
            $ifIndex = explode('.', $split)[13];
            $type = explode('.', $split)[14];

            //type8 = tx dBm
            if ($type == 8) {
                $value = $value / $divisor;
                $tmp = get_port_by_index_cache($device['device_id'], $ifIndex);
                $descr = $tmp['ifName'];
                discover_sensor(
                    $valid['sensor'], 'dbm', $device, $split, 'txdbm' . $ifIndex, 'rlPhyTestTableTxOutput', 'SfpTxdBm-' . $descr, $divisor, '1', $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $value
                );
            }

            //type9 = rx dBm
            if ($type == 9) {
                $value = $value / $divisor;
                $tmp = get_port_by_index_cache($device['device_id'], $ifIndex);
                $descr = $tmp['ifName'];
                discover_sensor(
                    $valid['sensor'], 'dbm', $device, $split, 'rxdbm' . $ifIndex, 'rlPhyTestTableRxOpticalPower', 'SfpRxdBm-' . $descr, $divisor, '1', $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $value
                );
            }
        }
    }
}
