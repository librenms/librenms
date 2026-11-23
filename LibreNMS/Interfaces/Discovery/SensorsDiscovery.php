<?php

/**
 * SensorsDiscovery.php
 *
 * Discover health sensors
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

namespace LibreNMS\Interfaces\Discovery;

use App\Models\Sensor;
use Illuminate\Support\Collection;
use LibreNMS\Exceptions\SnmpConnectionException;

interface SensorsDiscovery
{
    /**
     * Discover the sensors of every sensor class.
     * The Sensors module sets the poller_type of the sensors to that of the polling method providing them.
     * State sensors carry their state translations, see Sensor::withStates().
     * Call throwIfConnectionError() on the queries, so a device that did not answer is not mistaken for a device without sensors.
     *
     * @return Collection<int, Sensor>
     *
     * @throws SnmpConnectionException when the device did not answer, existing sensors are kept
     */
    public function discoverSensors(): Collection;
}
