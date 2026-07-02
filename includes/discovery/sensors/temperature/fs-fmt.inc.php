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
$mulitplier = 1;

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
                    $oid = '.1.3.6.1.4.1.40989.10.16.' . $card . '.2.' . $pn . '.9.0';
                    $descr = 'Card ' . $card . ' ' . chr($slot + 64) . $port . ' Temperature';
                    $index = 'OAP-C' . $card . '-OEO::vSFP' . chr($slot + 64) . $port . 'ModeTemperature.0';
                    $current = SnmpQuery::get($oid)->value();
                    if (is_numeric($current)) {
                        discover_sensor(
                            null,
                            'temperature',
                            $device,
                            $oid,
                            $index,
                            'fs-fmt',
                            $descr,
                            $divisor,
                            $multiplier,
                            null,
                            null,
                            null,
                            null,
                            ($current / $divisor) * $multiplier,
                            'snmp'
                        );
                    }
                }
            }
        }
    }
}
