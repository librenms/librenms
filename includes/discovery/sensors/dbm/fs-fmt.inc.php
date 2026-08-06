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
$divisor = 100;
$multiplier = 1;

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
                    $oid_tx = '.1.3.6.1.4.1.40989.10.16.' . $card . '.2.' . $pn . '.4.0';
                    $descr_tx = 'Card ' . $card . ' ' . chr($slot + 64) . $port . ' Tx Power';
                    $index_tx = 'OAP-C' . $card . '-OEO::vSFP' . chr($slot + 64) . $port . 'TxPower.0';
                    $current_tx = SnmpQuery::get($oid_tx)->value();
                    if (is_numeric($current_tx)) {
                        discover_sensor(
                            null,
                            'dbm',
                            $device,
                            $oid_tx,
                            $index_tx,
                            'fs-fmt',
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
                    $oid_rx = '.1.3.6.1.4.1.40989.10.16.' . $card . '.2.' . $pn . '.5.0';
                    $descr_rx = 'Card ' . $card . ' ' . chr($slot + 64) . $port . ' Rx Power';
                    $index_rx = 'OAP-C' . $card . '-OEO::vSFP' . chr($slot + 64) . $port . 'RxPower.0';
                    $current_rx = SnmpQuery::get($oid_rx)->value();
                    $oid_rx_threshold = '.1.3.6.1.4.1.40989.10.16.' . $card . '.2.' . $pn . '.13.0';
                    $current_rx_threshold = SnmpQuery::get($oid_rx_threshold)->value();
                    if (is_numeric($current_rx)) {
                        discover_sensor(
                            null,
                            'dbm',
                            $device,
                            $oid_rx,
                            $index_rx,
                            'fs-fmt',
                            $descr_rx,
                            $divisor,
                            $multiplier,
                            ($current_rx_threshold / $divisor) * $multiplier,
                            null,
                            null,
                            null,
                            ($current_rx / $divisor) * $multiplier,
                            'snmp'
                        );
                    }
                }
            }
        }
    }
}
