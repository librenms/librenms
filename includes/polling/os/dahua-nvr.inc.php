<?php
/**
 * dahua-nvr.inc.php
 *
 * Dahua NVR OS polling
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
 * @copyright  2018 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

$dahua_data = snmp_get_multi_oid($device, ['softwareRevision.0', 'serialNumber.0', 'deviceType.0'], '-OUQs', 'DAHUA-SNMP-MIB');

$version  = $dahua_data['softwareRevision.0'];
$serial   = $dahua_data['serialNumber.0'];
$hardware = $dahua_data['deviceType.0'];

unset($dahua_data);
