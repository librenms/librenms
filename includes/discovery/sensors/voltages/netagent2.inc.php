<?php
/**
 * sinetica.inc.php
 *
 * -Description-
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
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */


if ($device['os'] == 'netagent2') {
    $in_voltage_oid = '.1.3.6.1.4.1.935.1.1.1.3.2.1.0';
    $in_voltage = snmp_get($device, $in_voltage_oid, '-Oqv');

    if (!empty($in_voltage) || $in_voltage == 0) {
        $type           = 'netagent2';
        $index          = 0;
        $limit          = 300;
        $warnlimit      = 253;
        $lowlimit       = 0;
        $lowwarnlimit   = 216;
        $divisor        = 10;
        $voltage        = $in_voltage / $divisor;
        $descr          = 'Input Voltage';

        discover_sensor(
            $valid['sensor'],
            'voltage',
            $device,
            $in_voltage_oid,
            $index,
            $type,
            $descr,
            $divisor,
            '1',
            $lowlimit,
            $lowwarnlimit,
            $warnlimit,
            $limit,
            $voltage
        );
    }
    
    $out_voltage_oid = '.1.3.6.1.4.1.935.1.1.1.4.2.1.0';
    $out_voltage = snmp_get($device, $out_voltage_oid, '-Oqv');

    if (!empty($out_voltage) || $out_voltage == 0) {
        $type           = 'netagent2';
        $index          = 0;
        $limit          = 300;
        $warnlimit      = 253;
        $lowlimit       = 0;
        $lowwarnlimit   = 216;
        $divisor        = 10;
        $voltage        = $out_voltage / $divisor;
        $descr          = 'Output Voltage';

        discover_sensor(
            $valid['sensor'],
            'voltage',
            $device,
            $in_voltage_oid,
            $index,
            $type,
            $descr,
            $divisor,
            '1',
            $lowlimit,
            $lowwarnlimit,
            $warnlimit,
            $limit,
            $voltage
        );
    }
}//end if
