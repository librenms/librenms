<?php
/**
 * e3meterdc.inc.php
 *
 * LibreNMS os poller module for Zyxel devices
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
 * @copyright  2020 PipoCanaja
 * @author     PipoCanaja
 */

$oids = ['NETTRACK-E3METER-CTR-SNMP-MIB::e3ConcentratorFWVersion'];
$e3meter = snmp_get_multi_oid($device, $oids, '-OUQnt');

$version = $e3meter['.1.3.6.1.4.1.21695.1.10.1.1'];

unset(
    $e3meter
);
