<?php

/**
 * Dxmos.php
 *
 * XKL DXM
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use Illuminate\Database\Eloquent\Collection;
use LibreNMS\OS;
use SnmpQuery;

class Dxmos extends OS
{
    /**
     * BER is a mantissa (times 100) and an exponent, read both from the table of the sensor
     */
    public function pollSensors(Collection $sensors): array
    {
        $values = parent::pollSensors($sensors);
        $ber = $sensors->where('sensor_class', 'ber');

        if ($ber->isNotEmpty()) {
            $data = SnmpQuery::walk(['XKL-MIB::xklWaveHostSideRxBERTable', 'XKL-MIB::xklWaveHostSideTxBERTable'])->valuesByIndex();

            foreach ($ber as $sensor) {
                if (preg_match('/^xklWaveHostSide([RT]x)BERPreFECCurrentMantissa\.(.+)$/', (string) $sensor->sensor_index, $matches)) {
                    $mantissa = $data[$matches[2]]["XKL-MIB::xklWaveHostSide{$matches[1]}BERPreFECCurrentMantissa"] ?? null;
                    $exponent = $data[$matches[2]]["XKL-MIB::xklWaveHostSide{$matches[1]}BERPreFECCurrentExponent"] ?? null;

                    if (is_numeric($mantissa) && is_numeric($exponent)) {
                        $values[$sensor->sensor_id] = $mantissa / 100 * 10 ** (int) $exponent;
                    }
                }
            }
        }

        return $values;
    }
}
