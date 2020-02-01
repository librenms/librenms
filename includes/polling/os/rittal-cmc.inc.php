<?php
/**
 * rittal-cmc.inc.php
 *
 * LibreNMS os poller module for Rittal CMC-TC/LCP-InlineEC  devices
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
 * @copyright  2020 Kevin Zink
 * @author     Kevin Zink <kevin.zink@mpi-hd.mpg.de>
 */

$descr = snmp_get($device, '1.3.6.1.2.1.1.1.0', '-OUQnt');

$match=[];
preg_match('/ SN ([0-9]*)/',$descr,$match);
$serial   = $match[1];

preg_match('/ HW V([0-9\.]*)/',$descr,$match);
$hardware = $match[1];

preg_match('/ SW V([0-9\.]*)/',$descr,$match);
$version = '[' . $match[1] . ']';

unset(
    $descr
);

