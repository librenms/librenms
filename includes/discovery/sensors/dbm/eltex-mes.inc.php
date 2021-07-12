<?php
/*
 * LibreNMS discovery module for Eltex-MES SFP Dbm
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

$oids = snmp_walk($device, '1.3.6.1.4.1.89.90.1.2.1.3', '-Osqn', '');
$oids = trim($oids);

if ($oids) {
    echo "Eltex-MES dBm:\n";

    foreach (explode("\n", $oids) as $data) {
        if ($data) {
            print_r($data);
            echo "\n";
            $split = trim(explode(' ', $data)[0]);
            $value = trim(explode(' ', $data)[1]);
            $ifIndex = explode('.', $split)[13];
            $type = explode('.', $split)[14];

            //type8 = tx dBm
            if ($type == 8) {
                $descr_oid = '1.0.8802.1.1.2.1.3.7.1.3.' . $ifIndex;
                $descr = trim(snmp_get($device, $descr_oid, '-Oqv', ''), '"');
                $value = $value / $divisor;
                discover_sensor($valid['sensor'], 'dbm', $device, $split, 'txdbm' . $ifIndex, 'eltex-mes', 'SfpdBmTx-' . $descr, $divisor, '1', $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $value);
            }

            //type9 = rx dBm
            if ($type == 9) {
                $descr_oid = '1.0.8802.1.1.2.1.3.7.1.3.' . $ifIndex;
                $descr = trim(snmp_get($device, $descr_oid, '-Oqv', ''), '"');
                $value = $value / $divisor;
                discover_sensor($valid['sensor'], 'dbm', $device, $split, 'rxdbm' . $ifIndex, 'eltex-mes', 'SfpdBmRx-' . $descr, $divisor, '1', $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $value);
            }
        }
    }
}
