<?php

/**
 * infinera-groove.inc.php
 *
 * LibreNMS BER discovery module for Infinera Groove
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
 * @copyright  2019 Nick Hilliard
 * @author     Nick Hilliard <nick@foobar.org>
 *
 * Modified for FEC, Magnus Bergroth
 */
foreach ($pre_cache['infineragroove_portTable'] as $index => $data) {
    $portAliasIndex = preg_replace('/\.0$/', '', $index);
    $group = (string)$pre_cache['infineragroove_portTable'][$portAliasIndex]['portAlias'];

    if (isset($data['bitErrorRatePreFecInstant']) && is_numeric($data['bitErrorRatePreFecInstant']) && $pre_cache['infineragroove_portTable'][$portAliasIndex]['portOperStatus'] == "up") {
        $descr = 'PreFecBer';
        $oid = '.1.3.6.1.4.1.42229.1.2.13.1.1.1.1.' . $index;
        $value = $data['bitErrorRatePreFecInstant'];
        $divisor = 1;
        discover_sensor(null, 'ber', $device, $oid, 'bitErrorRatePreFecInstant.' . $index, 'infinera-groove', $descr, $divisor, '1', null, null, null, null, $value, 'snmp', null, null, null, $group, 'GAUGE');
    }
    if (isset($data['bitErrorRatePostFecInstant']) && is_numeric($data['bitErrorRatePostFecInstant']) && $pre_cache['infineragroove_portTable'][$portAliasIndex]['portOperStatus'] == "up") {
        $descr = 'PostFecBer';
        $oid = '.1.3.6.1.4.1.42229.1.2.13.2.1.1.1.' . $index;
        $value = $data['bitErrorRatePostFecInstant'];
        $divisor = 1;
        discover_sensor(null, 'ber', $device, $oid, 'bitErrorRatePostFecInstant.' . $index, 'infinera-groove', $descr, $divisor, '1', null, null, null, null, $value, 'snmp', null, null, null, $group, 'GAUGE');
    }
}
