<?php

/**
 * Ctm.php
 *
 * CTM
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

class Ctm extends OS
{
    /**
     * Port states are a comma separated list, the sensor index is the port
     */
    public function pollSensors(Collection $sensors): array
    {
        $values = parent::pollSensors($sensors);

        foreach ($sensors->where('sensor_class', 'state')->whereIn('sensor_type', ['portOnM', 'portSyncM', 'portSyncS', 'portOnS']) as $sensor) {
            if (isset($values[$sensor->sensor_id])) {
                $values[$sensor->sensor_id] = explode(',', (string) $values[$sensor->sensor_id])[(int) $sensor->sensor_index - 1] ?? null;
            }
        }

        return array_filter($values, fn ($value) => $value !== null);
    }
}
