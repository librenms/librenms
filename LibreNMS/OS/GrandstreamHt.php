<?php

/**
 * GrandstreamHt.php
 *
 * -Description-
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
 * @copyright  2025 LibreNMS
 * @author     LibreNMS Contributors
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class GrandstreamHt extends OS implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        $statuses = SnmpQuery::hideMib()->get(['GS-HT8XX-MIB::versionCore.0', 'GS-HT8XX-MIB::versionCore.0.0', 'GS-HT8XX-MIB::versionBase.0', 'GS-HT8XX-MIB::versionBase.0.0', 'GS-HT8XX-MIB::PartNo.0', 'GS-HT8XX-MIB::PartNo.0.0'])->values();
        if (isset($statuses['versionCore.0']) && isset($statuses['versionBase.0'])) {
            $device->version = "Core: {$statuses['versionCore.0']}, Base: {$statuses['versionBase.0']}";
        }
        if (isset($statuses['versionCore.0.0']) && isset($statuses['versionBase.0.0'])) {
            $device->version = "Core: {$statuses['versionCore.0.0']}, Base: {$statuses['versionBase.0.0']}";
        }
        if (isset($statuses['PartNo.0']) || isset($statuses['PartNo.0.0'])) {
            $device->serial = $statuses['PartNo.0'] ?? $statuses['PartNo.0.0'];
        }
    }
}
