<?php

/**
 * fs-nmu.inc.php
 *
 * -Description-
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
 * @copyright  2026 Rob J. Epping
 * @author     RobJE <librenms@renf.us>
 */
$alarm_states_name = 'fsfmtalarmstate';
$alarm_states = [
    ['value' => 0, 'generic' => 2, 'descr' => 'alarm'],
    ['value' => 1, 'generic' => 0, 'descr' => 'normal'],
];
create_state_index($alarm_states_name, $alarm_states);

$buzzer_states_name = 'fsfmtbuzzerstate';
$buzzer_states = [
    ['value' => 0, 'generic' => 0, 'descr' => 'off'],
    ['value' => 1, 'generic' => 2, 'descr' => 'on'],
];
create_state_index($buzzer_states_name, $buzzer_states);

$power_states_name = 'fsfmtpowerstate';
$power_states = [
    ['value' => 0, 'generic' => 2, 'descr' => 'off'],
    ['value' => 1, 'generic' => 0, 'descr' => 'on'],
];
create_state_index($power_states_name, $power_states);

$divisor = 1;
$multiplier = 1;

// OAP-NMU states
$oid_power1 = '.1.3.6.1.4.1.40989.10.16.20.11.0';
$descr_power1 = 'Power 1 State';
$index_power1 = 'OAP-NMU::power1State.0';
$current_power1 = SnmpQuery::get($oid_power1)->value();
if (is_numeric($current_power1)) {
    discover_sensor(
        null,
        'state',
        $device,
        $oid_power1,
        $index_power1,
        $power_states_name,
        $descr_power1,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        ($current_power1 / $divisor) * $multiplier,
        'snmp'
    );
}

$oid_power2 = '.1.3.6.1.4.1.40989.10.16.20.12.0';
$descr_power2 = 'Power 2 State';
$index_power2 = 'OAP-NMU::power2State.0';
$current_power2 = SnmpQuery::get($oid_power2)->value();
if (is_numeric($current_power2)) {
    discover_sensor(
        null,
        'state',
        $device,
        $oid_power2,
        $index_power2,
        $power_states_name,
        $descr_power2,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        ($current_power2 / $divisor) * $multiplier,
        'snmp'
    );
}

$oid_fan = '.1.3.6.1.4.1.40989.10.16.20.12.0';
$descr_fan = 'Fan State';
$index_fan = 'OAP-NMU::fanState.0';
$current_fan = SnmpQuery::get($oid_fan)->value();
if (is_numeric($current_fan)) {
    discover_sensor(
        null,
        'state',
        $device,
        $oid_fan,
        $index_fan,
        $power_states_name,
        $descr_fan,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        ($current_fan / $divisor) * $multiplier,
        'snmp'
    );
}

$oid_buzzer = '.1.3.6.1.4.1.40989.10.16.20.8.0';
$descr_buzzer = 'Buzzer State';
$index_buzzer = 'OAP-NMU::buzzerState.0';
$current_buzzer = SnmpQuery::get($oid_buzzer)->value();
if (is_numeric($current_buzzer)) {
    discover_sensor(
        null,
        'state',
        $device,
        $oid_buzzer,
        $index_buzzer,
        $buzzer_states_name,
        $descr_buzzer,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        null,
        ($current_buzzer / $divisor) * $multiplier,
        'snmp'
    );
}

// there is a 4-slot, 8-slot and 16-slot unit. Tested on 4-slot only
for ($card = 1; $card <= 4; $card++) {
    $cardstate = SnmpQuery::get('.1.3.6.1.4.1.40989.10.16.' . $card . '.2.1.0')->value();
    if ($cardstate == 1) {
        for ($slot = 1; $slot <= 4; $slot++) {
            for ($port = 1; $port <= 2; $port++) {
                $pn = 10 + (($slot - 1) * 2) + $port;
                $oid_state = '.1.3.6.1.4.1.40989.10.16.' . $card . '.2.' . $pn . '.12.0';
                $current_state = SnmpQuery::get($oid_state)->value();
                if ($current_state == 1) {
                    $oid_tx = '.1.3.6.1.4.1.40989.10.16.' . $card . '.2.' . $pn . '.10.0';
                    $descr_tx = 'Card ' . $card . ' ' . chr($slot + 64) . $port . ' Tx Power Alarm';
                    $index_tx = 'OAP-C' . $card . '-OEO::vSFP' . chr($slot + 64) . $port . 'TxPowerAlarm.0';
                    $current_tx = SnmpQuery::get($oid_tx)->value();
                    if (is_numeric($current_tx)) {
                        discover_sensor(
                            null,
                            'state',
                            $device,
                            $oid_tx,
                            $index_tx,
                            $alarm_states_name,
                            $descr_tx,
                            $divisor,
                            $multiplier,
                            null,
                            null,
                            null,
                            null,
                            ($current_tx / $divisor) * $multiplier,
                            'snmp'
                        );
                    }
                    $oid_rx = '.1.3.6.1.4.1.40989.10.16.' . $card . '.2.' . $pn . '.11.0';
                    $descr_rx = 'Card ' . $card . ' ' . chr($slot + 64) . $port . ' Rx Power Alarm';
                    $index_rx = 'OAP-C' . $card . '-OEO::vSFP' . chr($slot + 64) . $port . 'RxPowerAlarm.0';
                    $current_rx = SnmpQuery::get($oid_rx)->value();
                    if (is_numeric($current_rx)) {
                        discover_sensor(
                            null,
                            'state',
                            $device,
                            $oid_rx,
                            $index_rx,
                            $alarm_states_name,
                            $descr_rx,
                            $divisor,
                            $multiplier,
                            null,
                            null,
                            null,
                            null,
                            ($current_rx / $divisor) * $multiplier,
                            'snmp'
                        );
                    }
                    $oid_temp = '.1.3.6.1.4.1.40989.10.16.' . $card . '.2.' . $pn . '.1.0';
                    $descr_temp = 'Card ' . $card . ' ' . chr($slot + 64) . $port . ' Temperature Alarm';
                    $index_temp = 'OAP-C' . $card . '-OEO::vSFP' . chr($slot + 64) . $port . 'ModeTemperatureAlarm.0';
                    $current_temp = SnmpQuery::get($oid_temp)->value();
                    if (is_numeric($current_temp)) {
                        discover_sensor(
                            null,
                            'state',
                            $device,
                            $oid_temp,
                            $index_temp,
                            $alarm_states_name,
                            $descr_temp,
                            $divisor,
                            $multiplier,
                            null,
                            null,
                            null,
                            null,
                            ($current_temp / $divisor) * $multiplier,
                            'snmp'
                        );
                    }
                }
            }
        }
    }
}
