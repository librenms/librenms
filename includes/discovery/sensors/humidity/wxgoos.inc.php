<?php
/**
 * wxgoos.inc.php
 *
 * LibreNMS humidity discovery module for ITWatchdogs Goose
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

use LibreNMS\Util\Number;

$value = Number::cast(SnmpQuery::get('IT-WATCHDOGS-MIB-V3::climateHumidity')->value());
if ($value) {
    $current_oid = '.1.3.6.1.4.1.17373.3.2.1.7.1';
    $descr = 'Humidity';
    discover_sensor($valid['sensor'], 'humidity', $device, $current_oid, 'climateHumidity', 'wxgoos', $descr, 1, 1, null, null, null, null, $value);
}
