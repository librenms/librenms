<?php

/**
 * AristaMos.php
 *
 * Arista Metamako MOS
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

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use LibreNMS\OS\Traits\NetSnmpSensors;

class AristaMos extends Shared\Arista
{
    use NetSnmpSensors;

    /**
     * Early (circa 0.16.x) versions report negated fan speeds
     */
    public function pollSensors(EloquentCollection $sensors): array
    {
        $values = parent::pollSensors($sensors);

        foreach ($sensors->where('sensor_class', 'fanspeed') as $sensor) {
            $value = $values[$sensor->sensor_id] ?? null;
            if (is_numeric($value) && $value >= 2 ** 31 && str_starts_with((string) $sensor->sensor_oid, '.1.3.6.1.4.1.2021.13.16.3.1.3.')) {
                $values[$sensor->sensor_id] = ((int) $value ^ 0xFFFFFFFF) + 1;
            }
        }

        return $values;
    }

    /**
     * LM-SENSORS-MIB and ups-nut fan, temperature and voltage sensors of the Linux base
     */
    protected function discoverOsSensors(): Collection
    {
        return parent::discoverOsSensors()->merge($this->discoverNetSnmpSensors()->whereIn('sensor_class', ['fanspeed', 'temperature', 'voltage']));
    }
}
