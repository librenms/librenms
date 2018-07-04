<?php
/**
 * raritan-pdu.inc.php
 *
 * LibreNMS temperature sensor discovery module for Raritan
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

$index = 'unitCpuTemp.0';
$oid = '.1.3.6.1.4.1.13742.4.1.3.1.5.0';
$descr = 'Processor Temp';
$divisor = 10;
$raritan_data = snmp_get_multi_oid($device, 'unitCpuTemp.0 unitTempLowerWarning.0 unitTempLowerCritical.0 unitTempUpperWarning.0 unitTempUpperCritical.0', '-OUQs', 'PDU-MIB');
if (is_array($raritan_data) && !empty($raritan_data)) {
    $low_limit = $raritan_data['unitTempLowerCritical.0'];
    $low_warn_limit = $raritan_data['unitTempLowerWarning.0'];
    $warn_limit = $raritan_data['unitTempUpperWarning.0'];
    $high_limit = $raritan_data['unitTempUpperCritical.0'];
    $current = $raritan_data['unitCpuTemp.0'] / $divisor;
    discover_sensor($valid["sensor"], "temperature", $device, $oid, $tmp_index, 'raritan', $descr, $divisor, 1, $low_limit, $low_limit, $warn_limit, $high_limit, $current);
}

// Look for an external 1-wire sensor
$external = snmp_get($device, '.1.3.6.1.4.1.13742.6.3.6.5.1.4.1.1', '-Ovq', 'PDU2-MIB');
if ($external == "DPX-T1H1") {
    $oid = '.1.3.6.1.4.1.13742.6.5.5.3.1.4.1.1';
    $descr = 'External Sensor';
    $low_limit = snmp_get($device, '.1.3.6.1.4.1.13742.6.3.6.3.1.31.1.1', '-Ovq', 'PDU2-MIB') / $divisor;
    $low_warn_limit = snmp_get($device, '.1.3.6.1.4.1.13742.6.3.6.3.1.32.1.1', '-Ovq', 'PDU2-MIB') / $divisor;
    $warn_limit = snmp_get($device, '.1.3.6.1.4.1.13742.6.3.6.3.1.34.1.1', '-Ovq', 'PDU2-MIB') / $divisor;
    $high_limit = snmp_get($device, '.1.3.6.1.4.1.13742.6.3.6.3.1.33.1.1', '-Ovq', 'PDU2-MIB') / $divisor;
    $current = snmp_get($device, '.1.3.6.1.4.1.13742.6.5.5.3.1.4.1.1', '-Ovq', 'PDU2-MIB') / $divisor;
    discover_sensor($valid["sensor"], "temperature", $device, $oid, $tmp_index, 'raritan', $descr, $divisor, 1, $low_limit, $low_limit, $warn_limit, $high_limit, $current);
}
