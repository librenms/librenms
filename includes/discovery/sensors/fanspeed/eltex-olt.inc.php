<?php
/**
 * eltex-olt.inc.php
 *
 * LibreNMS fanspeed discovery module for Eltex OLT
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */
$tmp_eltex = snmp_get_multi_oid($device, 'ltp8xFan0Active.0 ltp8xFan0RPM.0 ltp8xFan1Active.0 ltp8xFan1RPM.0 ltp8xFanMinRPM.0 ltp8xFanMaxRPM.0', '-OUQn', 'ELTEX-LTP8X-STANDALONE');

$min_eltex = $tmp_eltex['.1.3.6.1.4.1.35265.1.22.1.10.20.0'] ?: null;
$max_eltex = $tmp_eltex['.1.3.6.1.4.1.35265.1.22.1.10.21.0'] ?: null;

if ($tmp_eltex['.1.3.6.1.4.1.35265.1.22.1.10.6.0']) {
    if (is_numeric($tmp_eltex['.1.3.6.1.4.1.35265.1.22.1.10.7.0'])) {
        $oid = '.1.3.6.1.4.1.35265.1.22.1.10.7.0';
        $index = 0;
        $type = 'eltex-olt';
        $descr = 'Fan 0';
        $divisor = 1;
        $fanspeed = $tmp_eltex[$oid];
        discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, $index, $type, $descr, $divisor, '1', $min_eltex, null, null, $max_eltex, $fanspeed);
    }
}

if ($tmp_eltex['.1.3.6.1.4.1.35265.1.22.1.10.8.0']) {
    if (is_numeric($tmp_eltex['.1.3.6.1.4.1.35265.1.22.1.10.9.0'])) {
        $oid = '.1.3.6.1.4.1.35265.1.22.1.10.9.0';
        $index = 1;
        $type = 'eltex-olt';
        $descr = 'Fan 1';
        $divisor = 1;
        $fanspeed = $tmp_eltex[$oid];
        discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, $index, $type, $descr, $divisor, '1', $min_eltex, null, null, $max_eltex, $fanspeed);
    }
}

unset($tmp_eltex);
