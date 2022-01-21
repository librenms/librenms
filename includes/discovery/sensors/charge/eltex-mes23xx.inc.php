<?php
/*
 * LibreNMS discovery module for Eltex-MES23xx Battery charge
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
 * @copyright  2021 Peca Nesovanovic
 *
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */
$oids = snmp_walk($device, 'eltEnvMonBatteryStatusCharge', '-Osqn', 'ELTEX-MES-HWENVIROMENT-MIB');
$oids = trim($oids);

if ($oids) {
    d_echo('Eltex-MES charge');
    foreach (explode("\n", $oids) as $data) {
        if ($data) {
            $oid = trim(explode(' ', $data)[0]);
            $value = trim(explode(' ', $data)[1]);
            $index = trim(explode('.', $oid)[14]);
            if ($value <= 100) { // value > 100 if there is no battery connected
                $type = 'eltex-mes23xx';
                $limit = 101;
                $limitwarn = 100;
                $lowlimit = 0;
                $lowwarnlimit = 10;
                $descr = 'Battery Charge';

                discover_sensor($valid['sensor'], 'charge', $device, $oid, $index, $type, $descr, 1, 1, $lowlimit, $lowwarnlimit, $limitwarn, $limit, $value);
            }
        }
    }
}
