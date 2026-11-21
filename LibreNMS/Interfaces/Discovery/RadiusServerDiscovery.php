<?php

/**
 * RadiusServerDiscovery.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Interfaces\Discovery;

use App\Models\RadiusServer;
use Illuminate\Support\Collection;

interface RadiusServerDiscovery
{
    /**
     * The RADIUS authentication servers of a device with subscribers, and their counters.
     * Called at discovery and polling, a few walks of small tables (one row per server).
     * The RadiusAuthClientMib trait implements the standard RADIUS-AUTH-CLIENT-MIB.
     * Returns null when the device did not answer, the servers are then kept as they are.
     *
     * @return Collection<int, RadiusServer>|null
     */
    public function discoverRadiusServers(): ?Collection;
}
