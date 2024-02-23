<?php
/**
 * unix.inc.php
 *
 * LibreNMS voltage sensor discovery module for UNIX based OS
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
 *
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */
use LibreNMS\Util\Oid;

$snmpData = SnmpQuery::cache()->hideMib()->walk('LM-SENSORS-MIB::lmSensors')->table(1);
if (! empty($snmpData)) {
    echo 'LM-SENSORS-MIB: ' . PHP_EOL;
    foreach ($snmpData as $lmData) {
        $type = 'lmVoltSensors';
        $divisor = 1000;
        $index = $lmData[$type . 'Index'];
        $descr = $lmData[$type . 'Device'];
        $value = intval($lmData[$type . 'Value']) / $divisor;
        if (! empty($descr)) {
            $oid = Oid::toNumeric('LM-SENSORS-MIB::' . $type . 'Value.' . $index);
            discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, 'lmsensors', $descr, $divisor, 1, null, null, null, null, $value, 'snmp', null, null, null, 'lmsensors');
        }
    }
}

$snmpData = SnmpQuery::cache()->hideMib()->walk('NET-SNMP-EXTEND-MIB::nsExtendOutLine."ups-nut"')->table(3);
if (! empty($snmpData)) {
    echo 'UPS-NUT-MIB: ' . PHP_EOL;
    $snmpData = array_shift($snmpData); //drop [ups-nut]
    $upsnut = [
        4 => ['descr' => 'Battery Voltage', 'LL' => 0, 'LW' => 0, 'W' => null, 'H' => 60],
        5 => ['descr' => 'Battery Nominal', 'LL' => 0, 'LW' => 0, 'W' => null, 'H' => 60],
        6 => ['descr' => 'Line Nominal', 'LL' => 0, 'LW' => 0, 'W' => null, 'H' => 0],
        7 => ['descr' => 'Input Voltage', 'LL' => 200, 'LW' => 0, 'W' => null, 'H' => 280],
    ];
    foreach ($snmpData as $index => $upsData) {
        if ($upsnut[$index]) {
            $value = $upsData['nsExtendOutLine'];
            if (is_numeric($value)) {
                $oid = Oid::toNumeric('NET-SNMP-EXTEND-MIB::nsExtendOutLine."ups-nut".' . $index);
                discover_sensor(
                    $valid['sensor'],
                    'voltage',
                    $device,
                    $oid,
                    $index,
                    'ups-nut',
                    $upsnut[$index]['descr'],
                    1,
                    1,
                    $upsnut[$index]['LL'],
                    $upsnut[$index]['LW'],
                    $upsnut[$index]['W'],
                    $upsnut[$index]['H'],
                    $value,
                    'snmp',
                    null,
                    null,
                    null,
                    'ups-nut'
                );
            }
        }
    }
}
