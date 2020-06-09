<?php
/**
 * gaia.inc.php
 *
 * LibreNMS OS poller module for Check Point GAIA
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

$tmp_gaia = snmp_get_multi_oid($device, ['svnVersion.0', 'svnApplianceProductName.0', 'svnApplianceSerialNumber.0'], '-OUQs', 'CHECKPOINT-MIB');
$serial   = $tmp_gaia['svnApplianceSerialNumber.0'];
$hardware = $tmp_gaia['svnApplianceProductName.0'];
$version  = $tmp_gaia['svnVersion.0'];
unset($tmp_gaia);
