<?php
/**
 * wxgoos.inc.php
 *
 * LibreNMS pre-cache poller module for ITWatchdogs Goose
 * Addapted from geist-watchdog.inc.php
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
 *
 * @copyright  2023 Dalen Catt
 * @author     Dalen Catt <dalencattmlsp@gmail.com>
 */
if ($type == 'temperature') {
    $sensor_cache['wxgoos_temp_unit'] = snmp_get($device, 'temperatureUnits.0', '-Oqv', 'IT-WATCHDOGS-MIB-V3');
}
