<?php
/**
 * fusion.inc.php
 *
 * LibreNMS OS poller module for IgniteNet FusionSwitch
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

$fusion_tmp = snmp_get_multi_oid($device, ['swSerialNumber.1', 'swOpCodeVer.1', 'swModelNumber.1'], '-OUQs', 'ES4552BH2-MIB');
$serial     = $fusion_tmp['swSerialNumber.1'];
$hardware   = $fusion_tmp['swModelNumber.1'];
$version    = $fusion_tmp['swOpCodeVer.1'];

unset($fusion_tmp);
