<?php

/**
 * Netapp.php
 *
 * NetApp
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

class Netapp extends OS
{
    /**
     * A shelf reports all its temperatures in one string, the second part of the index is the position
     */
    public function pollSensors(Collection $sensors): array
    {
        $values = parent::pollSensors($sensors);

        foreach ($sensors->where('sensor_class', 'temperature') as $sensor) {
            preg_match_all('/([0-9]+C)+/', (string) ($values[$sensor->sensor_id] ?? ''), $temperatures);
            $position = explode('.', (string) $sensor->sensor_index)[1] ?? null;
            $values[$sensor->sensor_id] = $temperatures[0][$position] ?? null;
        }

        return array_filter($values, fn ($value) => $value !== null);
    }
}
