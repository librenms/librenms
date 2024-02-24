<?php
/**
 * unix.inc.php
 *
 * LibreNMS fanspeed discovery module for UNIX based OS
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
        $type = 'lmFanSensors';
        $divisor = 1;
        $index = $lmData[$type . 'Index'];
        $descr = $lmData[$type . 'Device'];
        $value = intval($lmData[$type . 'Value']) / $divisor;
        if (! empty($descr)) {
            $oid = Oid::toNumeric('LM-SENSORS-MIB::' . $type . 'Value.' . $index);
            discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, $index, 'lmsensors', $descr, $divisor, 1, null, null, null, null, $value, 'snmp', null, null, null, 'lmsensors');
        }
    }
}
