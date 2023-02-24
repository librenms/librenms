<?php
/*
 * LibreNMS discovery module for Eltex-mes23xx SFP Dbm
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
$divisor = 1000;
$multiplier = 1;

if ($pre_cache['eltex-mes23xx-sfp']) {
    foreach ($pre_cache['eltex-mes23xx-sfp'] as $ifIndex => $data) {
        if (isset($data['rlPhyTestTableTxOutput']['rlPhyTestGetResult'])) {
            $value = $data['rlPhyTestTableTxOutput']['rlPhyTestGetResult'] / $divisor;
            if ($value) {
                $high_limit = $data['txOutput']['eltPhdTransceiverThresholdHighAlarm'] / $divisor;
                $high_warn_limit = $data['txOutput']['eltPhdTransceiverThresholdHighWarning'] / $divisor;
                $low_warn_limit = $data['txOutput']['eltPhdTransceiverThresholdLowWarning'] / $divisor;
                $low_limit = $data['txOutput']['eltPhdTransceiverThresholdLowAlarm'] / $divisor;
                $tmp = get_port_by_index_cache($device['device_id'], $ifIndex);
                $descr = $tmp['ifName'];
                $oid = '.1.3.6.1.4.1.89.90.1.2.1.3.' . $ifIndex . '.8';
                discover_sensor(
                    $valid['sensor'],
                    'dbm',
                    $device,
                    $oid,
                    'SfpTxDbm' . $ifIndex,
                    'rlPhyTestTableTxOutput',
                    'SfpTxDbm-' . $descr,
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
                    'Transceiver TX'
                );
            }
        }

        if (isset($data['rlPhyTestTableRxOpticalPower']['rlPhyTestGetResult'])) {
            $value = $data['rlPhyTestTableRxOpticalPower']['rlPhyTestGetResult'] / $divisor;
            if ($value) {
                $high_limit = $data['rxOpticalPower']['eltPhdTransceiverThresholdHighAlarm'] / $divisor;
                $high_warn_limit = $data['rxOpticalPower']['eltPhdTransceiverThresholdHighWarning'] / $divisor;
                $low_warn_limit = $data['rxOpticalPower']['eltPhdTransceiverThresholdLowWarning'] / $divisor;
                $low_limit = $data['rxOpticalPower']['eltPhdTransceiverThresholdLowAlarm'] / $divisor;
                $tmp = get_port_by_index_cache($device['device_id'], $ifIndex);
                $descr = $tmp['ifName'];
                $oid = '.1.3.6.1.4.1.89.90.1.2.1.3.' . $ifIndex . '.9';
                discover_sensor(
                    $valid['sensor'],
                    'dbm',
                    $device,
                    $oid,
                    'SfpRxDbm' . $ifIndex,
                    'rlPhyTestTableRxOpticalPower',
                    'SfpRxDbm-' . $descr,
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
                    'Transceiver RX'
                );
            }
        }
    }
}
