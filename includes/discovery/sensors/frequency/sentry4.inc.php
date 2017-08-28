<?php
/**
 * sentry4.inc.php
 *
 * LibreNMS frequency discovery module for Sentry4
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

foreach ($pre_cache['sentry4_input'] as $index => $data) {
    $descr   = $data['st4InputCordName'];
    $oid     = ".1.3.6.1.4.1.1718.4.1.3.3.1.11.$index";
    $current = $data['st4InputCordFrequency'];
    $divisor = 10;
    if ($current >= 0) {
        discover_sensor($valid['sensor'], 'frequency', $device, $oid, "st4InputCord.$index", 'sentry4', $descr, $divisor, 1, null, null, null, null, $current);
    }
}
