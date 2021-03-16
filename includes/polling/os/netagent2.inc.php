<?php
/**
 * netagent2.inc.php
 *
 * LibreNMS os polling module for Megatec NetAgent II and NetAgent Mini
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
 * @copyright  2018 Mikael Sipilainen
 * @author     Mikael Sipilainen <mikael.sipilainen@gmail.com>
 */
$oid = snmp_get_multi($device, ['.1.3.6.1.2.1.33.1.1.4.0', '.1.3.6.1.2.1.33.1.1.1.0'], '-OQU');
$version = $oid[0]['.1.3.6.1.2.1.33.1.1.4.0'];
$hardware = $oid[0]['.1.3.6.1.2.1.33.1.1.1.0'];
