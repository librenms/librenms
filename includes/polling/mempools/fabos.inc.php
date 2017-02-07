<?php
/**
 * fabos.inc.php
 *
 * LibreNMS mempool poller module for fabos
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
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

$perc = snmp_get($device, 'swMemUsage.0', '-Ovq', 'SW-MIB');

if (is_numeric($perc)) {
    $mempool['total'] = 100;
    $mempool['used'] = $perc;
    $mempool['free'] = ($mempool['total'] - $mempool['used']);
}
