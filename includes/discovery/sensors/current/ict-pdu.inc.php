<?php
/**
 * ict-pdu.inc.php
 *
 * LibreNMS current sensor discovery module for ICT DC Distribution Panel
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
 * @copyright  2017 Lorenzo Zafra
 * @author     Lorenzo Zafra<zafra@ualberta.ca>
 */

// Output Current
$oids = snmpwalk_cache_oid($device, 'outputEntry', array(), 'ICT-MIB');

foreach ($oids as $index => $entry) {
    $output_number = (int)$entry['outputNumber'] + 1;

    $descr = 'Output Current #' . $output_number;
    if ($entry['outputName'] && $entry['outputName'] != '00') {
        $descr .= ' ' . $entry['outputName'];
    }

    $divisor = 1;
    $oid = '.1.3.6.1.4.1.39145.10.8.1.3.'.$index;
    $type = 'ict-pdu';
    $current = (float)$entry['outputCurrent'] / $divisor;

    discover_sensor($valid['sensor'], 'current', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}

// System Current
$systemCurrent_oid = '.1.3.6.1.4.1.39145.10.7.0';
$systemCurrent = trim(snmp_get($device, $systemCurrent_oid, '-Oqv'), '" ');
if (!empty($systemCurrent)) {
    $divisor = 1;
    $index = 0;
    $descr = 'System Current';
    $type = 'ict-pdu';
    $current = $systemCurrent / $divisor;

    discover_sensor($valid['sensor'], 'current', $device, $systemCurrent_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}
