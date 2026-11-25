<?php

/**
 * DellPowervault.php
 *
 * Dell PowerVault
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

class DellPowervault extends OS
{
    /**
     * FIBRE-CHANNEL-MGMT-MIB sensor messages (dellme) end with the value, like "...: 35 C 95.00F"
     */
    public function pollSensors(Collection $sensors): array
    {
        $patterns = [
            'charge' => '/^ ([0-9]+)%$/',
            'current' => '/^ ([0-9]+\.[0-9]+)A$/',
            'temperature' => '/^ ([0-9]+) C ([0-9]+\.[0-9]+)F$/',
            'voltage' => '/^ ([0-9]+\.[0-9]+)V$/',
        ];
        $values = parent::pollSensors($sensors);

        foreach ($sensors->where('sensor_type', 'dellme') as $sensor) {
            if (! isset($values[$sensor->sensor_id])) {
                continue;
            }

            $message = substr((string) strrchr(':' . $values[$sensor->sensor_id], ':'), 1);
            if ($sensor->sensor_class == 'state') {
                $values[$sensor->sensor_id] = $message === ' OK' ? 1 : 2;
            } elseif (isset($patterns[$sensor->sensor_class])) {
                $values[$sensor->sensor_id] = preg_match($patterns[$sensor->sensor_class], $message, $matches) ? $matches[1] : null;
            }
        }

        return array_filter($values, fn ($value) => $value !== null);
    }
}
