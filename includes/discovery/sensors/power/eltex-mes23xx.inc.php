<?php
/*
 * LibreNMS discovery module for Eltex-MES23xx PoE power
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
if ($pre_cache['eltex-mes23xx-poe']) {
    foreach ($pre_cache['eltex-mes23xx-poe'] as $index => $data) {
        if (isset($data['rlPethPsePortOutputPower'])) {
            $value = $data['rlPethPsePortOutputPower'] / $divisor;
            if ($value) {
                $high_limit = $data['rlPethPsePortPowerLimit'] / $divisor;
                $high_warn_limit = ($data['rlPethPsePortPowerLimit'] / $divisor) * 0.8;
                $low_warn_limit = 0;
                $low_limit = 0;
                [$unit, $ifIndex] = explode('.', $index);
                $tmp = get_port_by_index_cache($device['device_id'], $ifIndex);
                $descr = $tmp['ifName'];
                $oid = '.1.3.6.1.4.1.89.108.1.1.5.' . $unit . '.' . $ifIndex;
                discover_sensor(
                    $valid['sensor'],
                    'power',
                    $device,
                    $oid,
                    'Poe' . $index, //unit.index
                    'rlPethPsePortOutputPower',
                    'PoE-' . $descr,
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
                    'PoE'
                );
            }
        }
    }
}
