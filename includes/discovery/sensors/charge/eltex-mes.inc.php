<?php
/*
 * LibreNMS discovery module for Eltex-MES Battery charge
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

$oids = snmp_walk($device, '1.3.6.1.4.1.35265.1.23.11.1.1.3', '-Osqn', '');
$oids = trim($oids);

if ($oids) {
    echo "Eltex-MES charge:\n";

    foreach (explode("\n", $oids) as $data) {
        if ($data) {
            print_r($data);
            echo "\n";
            $oid = trim(explode(' ', $data)[0]);
            $value = trim(explode(' ', $data)[1]);
            $index = trim(explode('.', $oid)[14]);

            $type = 'eltex-mes';
            $limit = 101;
            $limitwarn = 100;
            $lowlimit = 0;
            $lowwarnlimit = 10;
            $descr = 'Battery Charge';

            discover_sensor($valid['sensor'], 'charge', $device, $oid, $index, $type, $descr, 1, 1, $lowlimit, $lowwarnlimit, $limitwarn, $limit, $value);
        }
    }
}
