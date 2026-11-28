<?php

/**
 * Exos.php
 *
 * Extreme EXOS
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

class Exos extends OS
{
    /**
     * Values are in descriptive strings, like "... Temp: 45 C"
     */
    public function pollSensors(Collection $sensors): array
    {
        $patterns = [
            'current' => '/Current.* ([: 0-9.]+)A/',
            'temperature' => '/ Temp.* ([: 0-9]+) C/',
            'voltage' => '/Voltage.* ([: 0-9.]+)V/',
        ];
        $values = parent::pollSensors($sensors);

        foreach ($sensors as $sensor) {
            if (isset($patterns[$sensor->sensor_class]) && preg_match($patterns[$sensor->sensor_class], (string) ($values[$sensor->sensor_id] ?? ''), $matches)) {
                $values[$sensor->sensor_id] = trim($matches[1]);
            }
        }

        return $values;
    }
}
