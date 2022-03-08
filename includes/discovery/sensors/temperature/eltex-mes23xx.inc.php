<?php
/*
 * LibreNMS discovery module for Eltex-MES23xx SFP Temperature
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
 * @copyright  2022 Peca Nesovanovic
 *
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */
$divisor = 1;
$multiplier = 1;
if ($pre_cache['eltex-mes23xx-sfp']) {
    foreach ($pre_cache['eltex-mes23xx-sfp'] as $ifIndex => $data) {
        if (isset($data['rlPhyTestTableTransceiverTemp']['rlPhyTestGetResult'])) {
            $value = $data['rlPhyTestTableTransceiverTemp']['rlPhyTestGetResult'] / $divisor;
            if ($value) {
                $high_limit = $data['temperature']['eltPhdTransceiverThresholdHighAlarm'] / $divisor;
                $high_warn_limit = $data['temperature']['eltPhdTransceiverThresholdHighWarning'] / $divisor;
                $low_warn_limit = $data['temperature']['eltPhdTransceiverThresholdLowWarning'] / $divisor;
                $low_limit = $data['temperature']['eltPhdTransceiverThresholdLowAlarm'] / $divisor;
                $tmp = get_port_by_index_cache($device['device_id'], $ifIndex);
                $descr = $tmp['ifName'];
                $oid = '.1.3.6.1.4.1.89.90.1.2.1.3.' . $ifIndex . '.5';
                discover_sensor(
                    $valid['sensor'],
                    'temperature',
                    $device,
                    $oid,
                    'SfpTemp' . $ifIndex,
                    'rlPhyTestTableTransceiverTemp',
                    'SfpTemp-' . $descr,
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
}
