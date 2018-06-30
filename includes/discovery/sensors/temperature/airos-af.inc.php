<?php
/**
 * airos-af.inc.php
 *
 * Ubiquiti AirFiber Temperatures
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

$temps = snmp_get_multi($device, 'radio0TempC.1 radio1TempC.1', '-OQUs', 'UBNT-AirFIBER-MIB');

if (isset($temps[1]['radio0TempC'])) {
    discover_sensor(
        $valid['sensor'],
        'temperature',
        $device,
        '.1.3.6.1.4.1.41112.1.3.2.1.8.1',
        0,
        'airos-af',
        'Radio 0 Temp',
        1,
        1,
        null,
        null,
        null,
        null,
        $temps[1]['radio0TempC']
    );
}

if (isset($temps[1]['radio1TempC'])) {
    discover_sensor(
        $valid['sensor'],
        'temperature',
        $device,
        '.1.3.6.1.4.1.41112.1.3.2.1.10.1',
        1,
        'airos-af',
        'Radio 1 Temp',
        1,
        1,
        null,
        null,
        null,
        null,
        $temps[1]['radio1TempC']
    );
}

unset($temps);
