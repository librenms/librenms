<?php
/**
 * unix.inc.php
 *
 * LibreNMS load sensor discovery module for UNIX based OS
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

$snmpData = SnmpQuery::cache()->hideMib()->walk('NET-SNMP-EXTEND-MIB::nsExtendOutLine."ups-nut"')->table(3);
if (! empty($snmpData)) {
    echo 'UPS-NUT-MIB: ' . PHP_EOL;
    $snmpData = array_shift($snmpData); //drop [ups-nut]
    $upsnut = [
        8 => ['descr' => 'Ups Load', 'LL' => 0, 'LW' => 0, 'W' => null, 'H' => 100],
    ];
    foreach ($snmpData as $index => $upsData) {
        if ($upsnut[$index]) {
            $value = intval($upsData['nsExtendOutLine']);
            if (! empty($value)) {
                $oid = Oid::toNumeric('NET-SNMP-EXTEND-MIB::nsExtendOutLine."ups-nut".' . $index);
                discover_sensor(
                    $valid['sensor'],
                    'load',
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
