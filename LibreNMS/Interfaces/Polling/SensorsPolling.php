<?php

/**
 * SensorsPolling.php
 *
 * Poll health sensors
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

namespace LibreNMS\Interfaces\Polling;

use App\Models\Sensor;
use Illuminate\Database\Eloquent\Collection;
use LibreNMS\Exceptions\SnmpConnectionException;

interface SensorsPolling
{
    /**
     * Read the raw values of sensors, as the device returns them.
     * The Sensors module converts them with the divisor, multiplier and user_func of the sensor.
     * Sensors that could not be read are left out. The Sensors module records SNMP sensors that are left out as 0, it does not record others.
     * Call throwIfConnectionError() on the queries, so a device that did not answer is not recorded as 0.
     *
     * @param  Collection<int, Sensor>  $sensors
     * @return array<int, mixed> raw values by sensor_id
     *
     * @throws SnmpConnectionException when the device did not answer, the sensors are not recorded
     */
    public function pollSensors(Collection $sensors): array;
}
