<?php
/*
 * LibreNMS discovery module for junos Temperature
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 *
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */
echo 'JunOS ';
$oids = snmp_walk($device, '.1.3.6.1.4.1.2636.3.1.13.1.7', '-Osqn', 'JUNIPER-MIB', 'junos');
$oids = trim($oids);
foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    $data = substr($data, 29);
    if ($data) {
        [$oid] = explode(' ', $data);
        $temperature_oid = ".1.3.6.1.4.1.2636.3.1.13.1.7.$oid";
        $descr_oid = ".1.3.6.1.4.1.2636.3.1.13.1.5.$oid";
        $descr = snmp_get($device, $descr_oid, '-Oqv', 'JUNIPER-MIB', 'junos');
        $temperature = snmp_get($device, $temperature_oid, '-Oqv', 'JUNIPER-MIB', 'junos');
        if (! strstr($descr, 'No') && ! strstr($temperature, 'No') && $descr != '' && $temperature != '0') {
            $descr = str_replace('"', '', $descr);
            $descr = str_replace('temperature', '', $descr);
            $descr = str_replace('temperature', '', $descr);
            $descr = str_replace('sensor', '', $descr);
            $descr = trim($descr);

            discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $oid, 'junos', $descr, '1', '1', null, null, null, null, $temperature);
        }
    }
}

$multiplier = 1;
$divisor = 1;
foreach ($pre_cache['junos_oids'] as $index => $entry) {
    if (is_numeric($entry['jnxDomCurrentModuleTemperature']) && $entry['jnxDomCurrentModuleTemperature'] != 0 && $entry['jnxDomCurrentModuleTemperatureLowAlarmThreshold']) {
        $oid = '.1.3.6.1.4.1.2636.3.60.1.1.1.1.8.' . $index;
        $interface = get_port_by_index_cache($device['device_id'], $index)['ifDescr'];
        $descr = $interface . ' Temperature';
        $limit_low = $entry['jnxDomCurrentModuleTemperatureLowAlarmThreshold'] / $divisor;
        $warn_limit_low = $entry['jnxDomCurrentModuleTemperatureLowWarningThreshold'] / $divisor;
        $limit = $entry['jnxDomCurrentModuleTemperatureHighAlarmThreshold'] / $divisor;
        $warn_limit = $entry['jnxDomCurrentModuleTemperatureHighWarningThreshold'] / $divisor;
        $current = $entry['jnxDomCurrentModuleTemperature'];
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';

        discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'rx-' . $index, 'junos', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
    }
}
