<?php

/**
 * xkl.inc.php
 *
 * -Description-
 *
 * XKL sends BER in two OIDS. The first contains an integer value
 * multiplied by 100. The second is a mantissa exponent.
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
 * @copyright  2026 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */
echo 'XKL BER';

$rxData = SnmpQuery::walk('XKL-MIB::xklWaveHostSideRxBERTable')->valuesByIndex();
$txData = SnmpQuery::walk('XKL-MIB::xklWaveHostSideTxBERTable')->valuesByIndex();

foreach ($rxData as $index => $entry) {
    if ($entry['XKL-MIB::xklWaveHostSideRxBERWaveStatus'] != 2 && $entry['XKL-MIB::xklWaveHostSideRxBERPreFECCurrentMantissa'] != 0) {
        $oid = '.1.3.6.1.4.1.21150.1.1.39.1.7.' . $index;
        $mantissa = $entry['XKL-MIB::xklWaveHostSideRxBERPreFECCurrentMantissa'] / 100;
        $exponent = intval($entry['XKL-MIB::xklWaveHostSideRxBERPreFECCurrentExponent']);
        $rxBer = $mantissa * pow(10, $exponent);
        $waveDescr = $entry['XKL-MIB::xklWaveHostSideRxBERWaveDescr'];
			
        discover_sensor(
            null,
            'ber',
            $device,
            $oid,
            'xklWaveHostSideRxBERPreFECCurrentMantissa.' . $index,
            'xkl',
            $waveDescr . ' Hostside RX Pre-FEC BER',
            '1',
            '1',
            null,
            null,
            null,
            null,
            $rxBer,
        );
    }
}

foreach ($txData as $index => $entry) {
    if ($entry['XKL-MIB::xklWaveHostSideTxBERWaveStatus'] != 2 && $entry['XKL-MIB::xklWaveHostSideTxBERPreFECCurrentMantissa'] != 0) {
        $oid = '.1.3.6.1.4.1.21150.1.1.39.1.8.' . $index;
        $mantissa = $entry['XKL-MIB::xklWaveHostSideTxBERPreFECCurrentMantissa'] / 100;
        $exponent = intval($entry['XKL-MIB::xklWaveHostSideTxBERPreFECCurrentExponent']);
        $txBer = $mantissa * pow(10, $exponent);
        $waveDescr = $entry['XKL-MIB::xklWaveHostSideTxBERWaveDescr'];

        discover_sensor(
            null,
            'ber',
            $device,
            $oid,
            'xklWaveHostSideTxBERPreFECCurrentMantissa.' . $index,
            'xkl',
            $waveDescr . ' Hostside Tx Pre-FEC BER',
            '1',
            '1',
            null,
            null,
            null,
            null,
            $txBer,
        );
    }
}
