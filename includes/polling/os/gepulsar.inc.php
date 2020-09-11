<?php
/**
 * gepulsar.inc.php
 *
 * LibreNMS os polling module for GE Power systems
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

$tmp_gepulsar = snmp_get_multi($device, ['ne843Ps1Verw.0', 'ne843Ps1Sn.0', 'ne843Ps1Brc.0'], '-OQUs', 'NE843-MIB');
$version = $tmp_gepulsar[0]['ne843Ps1Verw'];
$serial  = $tmp_gepulsar[0]['ne843Ps1Sn'];
$hardware = $tmp_gepulsar[0]['ne843Ps1Brc'];

unset($tmp_gepulsar);
