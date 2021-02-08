<?php
/**
 * eltex-olt.inc.php
 *
 * LibreNMS OS poller module for Eltex OLT
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
$tmp_eltex = snmp_get($device, 'ltp8xFirmwareRevision.0', '-Ovq', 'ELTEX-LTP8X-STANDALONE');
[$hardware, $tmp_eltex] = explode(':', $tmp_eltex);
$tmp_eltex = preg_split('/(software version| on)/', $tmp_eltex);
$version = $tmp_eltex['1'];

unset($tmp_eltex);
