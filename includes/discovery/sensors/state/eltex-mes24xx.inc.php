<?php

/*
 * LibreNMS discovery module for Eltex-MES24xx SFP Lost of signal
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
 * @copyright  2025 Peca Nesovanovic
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

use LibreNMS\OS;
use LibreNMS\Util\Oid;

if (empty($os)) {
    $os = OS::make($device);
}

if ($os instanceof \LibreNMS\OS\EltexMes24xx) {
    $map = array_flip($os->getIfIndexEntPhysicalMap()); // map ifindex -> entphy index
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

    //Create State Index
    $type = 'eltex-mes24xx';
    $states = [
        ['value' => 0, 'generic' => 0, 'graph' => 1, 'descr' => 'false'],
        ['value' => 1, 'generic' => 2, 'graph' => 1, 'descr' => 'true'],
    ];
    create_state_index($type, $states);

    foreach ($eltexPhyTransceiverDiagnosticTable['lossOfSignal'] ?? [] as $ifIndex => $data) {
        if (! empty($data['eltexPhyTransceiverDiagnosticUnits'])) {
            $value = $data['eltexPhyTransceiverDiagnosticCurrentValue'];
            $port = PortCache::getByIfIndex($ifIndex, $device['device_id']);
            $descr = $port?->ifName;
            $oid = Oid::of('ELTEX-PHY-MIB::eltexPhyTransceiverDiagnosticUnits.' . $ifIndex . '.6.1')->toNumeric();

            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'state',
                'sensor_oid' => $oid,
                'sensor_index' => 'SfpLoss' . $ifIndex,
                'sensor_type' => $type,
                'sensor_descr' => 'SfpLoss-' . $descr,
                'sensor_divisor' => $divisor,
                'sensor_multiplier' => $multiplier,
                'sensor_limit_low' => null,
                'sensor_limit_low_warn' => null,
                'sensor_limit_warn' => null,
                'sensor_limit' => 1,
                'sensor_current' => $value,
                'entPhysicalIndex' => $map[$ifIndex], //map ifindex -> entphy index
                'entPhysicalIndex_measured' => 'port',
                'user_func' => null,
                'group' => 'transceiver',
            ]));
        }
    }
}
