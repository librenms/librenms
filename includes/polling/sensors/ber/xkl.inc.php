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
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */
echo 'XKL BER';

$mantissa = $sensor_value / 100;

$exponent = intval(SnmpQuery::walk('XKL-MIB::xklWaveHostSideRxBERTable')->value('XKL-MIB::xklWaveHostSideRxBERPreFECCurrentExponent'));

if (str_starts_with('.1.3.6.1.4.1.21150.1.1.39.1.8.', $sensor['sensor_oid'])) {
    $exponent = intval(SnmpQuery::walk('XKL-MIB::xklWaveHostSideTxBERTable')->value('XKL-MIB::xklWaveHostSideTxBERPreFECCurrentExponent'));
}

$sensor_value = $mantissa * pow(10, $exponent);
