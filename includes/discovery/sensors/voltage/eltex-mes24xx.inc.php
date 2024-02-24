<?php
/*
 * LibreNMS discovery module for Eltex-MES24xx SFP Voltage
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
 * @copyright  2024 Peca Nesovanovic
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */
use LibreNMS\Util\Oid;

echo 'eltexPhyTransceiverDiagnosticTable' . PHP_EOL;
$snmpData = SnmpQuery::cache()->hideMib()->walk('ELTEX-PHY-MIB::eltexPhyTransceiverDiagnosticTable')->table(3);
if (! empty($snmpData)) {
    foreach ($snmpData as $index => $typeData) {
        foreach ($typeData as $type => $data) {
            $eltexPhyTransceiverDiagnosticTable[$type][$index] = array_shift($data);
        }
    }
}

$divisor = 1;
$multiplier = 1;
if (! empty($eltexPhyTransceiverDiagnosticTable['supplyVoltage'])) {
    foreach ($eltexPhyTransceiverDiagnosticTable['supplyVoltage'] as $ifIndex => $data) {
        $value = $data['eltexPhyTransceiverDiagnosticCurrentValue'] / $divisor;
        if ($value) {
            $high_limit = $data['eltexPhyTransceiverDiagnosticHighAlarmThreshold'] / 1000;
            $high_warn_limit = $data['eltexPhyTransceiverDiagnosticHighWarningThreshold'] / 1000;
            $low_warn_limit = $data['eltexPhyTransceiverDiagnosticLowWarningThreshold'] / 1000;
            $low_limit = $data['eltexPhyTransceiverDiagnosticLowAlarmThreshold'] / 1000;
            $descr = get_port_by_index_cache($device['device_id'], $ifIndex)['ifName'];
            $oid = Oid::toNumeric('ELTEX-PHY-MIB::eltexPhyTransceiverDiagnosticCurrentValue.' . $ifIndex . '.2.1');
            discover_sensor(
                $valid['sensor'],
                'voltage',
                $device,
                $oid,
                'SfpVolt' . $ifIndex,
                'ELTEX-PHY-MIB',
                $descr,
                $divisor,
                $multiplier,
                $low_limit,
                $low_warn_limit,
                $high_warn_limit,
                $high_limit,
                $value,
                'snmp',
                null,
                null,
                null,
                'Transceiver'
            );
        }
    }
}
