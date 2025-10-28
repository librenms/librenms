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

$oids = SnmpQuery::cache()->hideMib()->walk('ELTEX-MES-PHYSICAL-DESCRIPTION-MIB::eltPhdTransceiverThresholdTable')->table(2);
$oids = SnmpQuery::cache()->hideMib()->walk('RADLAN-PHY-MIB::rlPhyTestGetResult')->table(1, $oids);

$divisor = 1;
$multiplier = 1;

//Create State Index
$type = 'eltex-mes23xx';
$states = [
    ['value' => 0, 'generic' => 0, 'graph' => 1, 'descr' => 'false'],
    ['value' => 1, 'generic' => 2, 'graph' => 1, 'descr' => 'true'],
];
create_state_index($type, $states);

foreach ($oids as $ifIndex => $data) {
    if (isset($data['rlPhyTestGetResult']['rlPhyTestTableLOS'])) {
        $value = $data['rlPhyTestGetResult']['rlPhyTestTableLOS'];
        $port = PortCache::getByIfIndex($ifIndex, $device['device_id']);
        $descr = $port?->ifName;
        $oid = '.1.3.6.1.4.1.89.90.1.2.1.3.' . $ifIndex . '.11';

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
            'entPhysicalIndex' => DeviceCache::getPrimary()->entityPhysical()->where('ifIndex', $ifIndex)->value('entPhysicalIndex'),
            'entPhysicalIndex_measured' => 'port',
            'user_func' => null,
            'group' => 'transceiver',
        ]));
    }
}
