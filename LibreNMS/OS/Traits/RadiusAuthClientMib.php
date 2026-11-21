<?php

/**
 * RadiusAuthClientMib.php
 *
 * RADIUS authentication servers from the standard RADIUS-AUTH-CLIENT-MIB (RFC 2618/4668)
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

namespace LibreNMS\OS\Traits;

use App\Models\RadiusServer;
use Illuminate\Support\Collection;
use LibreNMS\Exceptions\SnmpException;
use SnmpQuery;

trait RadiusAuthClientMib
{
    /**
     * @return Collection<int, RadiusServer>|null
     */
    public function discoverRadiusServers(): ?Collection
    {
        return $this->discoverRadiusAuthClientServers();
    }

    /**
     * One walk of radiusAuthServerTable, the round trip time is in hundredths of a second
     *
     * @return Collection<int, RadiusServer>|null
     */
    protected function discoverRadiusAuthClientServers(): ?Collection
    {
        try {
            $table = SnmpQuery::hideMib()->numericIndex()->walk('RADIUS-AUTH-CLIENT-MIB::radiusAuthServerTable')->throwIfConnectionError()->table(1);
        } catch (SnmpException) {
            return null;
        }

        return collect($table)
            ->filter(fn (array $row) => ! empty($row['radiusAuthServerAddress']))
            ->map(fn (array $row) => new RadiusServer([
                'address' => $row['radiusAuthServerAddress'],
                'port' => (int) ($row['radiusAuthClientServerPortNumber'] ?? 0),
                'requests' => $row['radiusAuthClientAccessRequests'] ?? null,
                'accepts' => $row['radiusAuthClientAccessAccepts'] ?? null,
                'rejects' => $row['radiusAuthClientAccessRejects'] ?? null,
                'timeouts' => $row['radiusAuthClientTimeouts'] ?? null,
                'round_trip_time' => isset($row['radiusAuthClientRoundTripTime']) ? (int) $row['radiusAuthClientRoundTripTime'] * 10 : null,
            ]))->values();
    }
}
