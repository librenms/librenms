<?php
/**
 * helios.inc.php
 *
 * LibreNMS os polling module for Ignore HeliOS
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

$helio_oids = snmp_get_multi_oid($device, ['model.0', 'fwVersion.0'], '-OUQn', 'IGNITENET-MIB');

$hardware = $helio_oids['.1.3.6.1.4.1.47307.1.1.1.0'];
$version  = $helio_oids['.1.3.6.1.4.1.47307.1.1.3.0'];

unset($helio_oids);
