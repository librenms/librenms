<?php

/**
 * GeistWatchdog.php
 *
 * Geist Watchdog
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
use LibreNMS\Util\UserFuncHelper;
use SnmpQuery;

class GeistWatchdog extends OS
{
    /**
     * Temperatures (tenths) are in the configured unit, 0 is fahrenheit
     */
    public function pollSensors(Collection $sensors): array
    {
        $values = parent::pollSensors($sensors);
        $temperatures = $sensors->where('sensor_class', 'temperature');

        if ($temperatures->isNotEmpty() && (string) SnmpQuery::get('GEIST-V4-MIB::temperatureUnits.0')->value() === '0') {
            foreach ($temperatures as $sensor) {
                if (is_numeric($values[$sensor->sensor_id] ?? null)) {
                    $values[$sensor->sensor_id] = UserFuncHelper::apply('fahrenheit_to_celsius', $values[$sensor->sensor_id] / 10) * 10;
                }
            }
        }

        return $values;
    }
}
