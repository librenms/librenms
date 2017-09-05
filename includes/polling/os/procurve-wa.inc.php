<?php
/**
 * procurve-wa.inc.php
 *
 * LibreNMS os discovery module for Procurve WA
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

$procurve_wa = snmp_get_multi($device, 'hpWANRoutersDeviceProductName.0 hpWANRoutersDeviceSerialNumber.0 hpWANRoutersDeviceVersion.0', '-OQUs', 'HP-ICF-WAN-UNIT');

$hardware = str_replace('ProCurve Secure Router ', '', $procurve_wa[0]['hpWANRoutersDeviceProductName']);
$serial   = $procurve_wa[0]['hpWANRoutersDeviceSerialNumber'];
$version  = $procurve_wa[0]['hpWANRoutersDeviceVersion'];
