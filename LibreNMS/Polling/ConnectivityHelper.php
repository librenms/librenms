<?php

/*
 * ConnectivityHelper.php
 *
 * Helper to check polling method availability and module gating for a device.
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Polling;

use App\Models\Device;
use LibreNMS\Enum\PollingMethodType;

readonly class ConnectivityHelper
{
    public function __construct(
        private Device $device,
    ) {}

    public function isAvailable(): bool
    {
        if (! $this->device->exists && ! $this->device->relationLoaded('pollingMethods')) {
            return true;
        }

        if ($this->device->pollingMethods->isEmpty()) {
            return true;
        }

        foreach ($this->device->pollingMethods as $method) {
            if ($method->enabled && $method->affects_availability && ! $method->last_check_successful) {
                return false;
            }
        }

        return true;
    }

    public function hasAvailability(): bool
    {
        foreach ($this->device->pollingMethods as $method) {
            if ($method->enabled && $method->affects_availability) {
                return true;
            }
        }

        return false;
    }

    public function methodIsEnabled(PollingMethodType $type): bool
    {
        return (bool) $this->device->getPollingMethod($type)?->enabled;
    }

    public function methodIsAvailable(PollingMethodType $type): bool
    {
        $method = $this->device->getPollingMethod($type);

        return $method?->enabled && $method?->last_check_successful;
    }

    public function snmpIsEnabled(): bool
    {
        return $this->methodIsEnabled(PollingMethodType::Snmp);
    }

    public function snmpIsAvailable(): bool
    {
        return $this->methodIsAvailable(PollingMethodType::Snmp);
    }

    public function ipmiIsEnabled(): bool
    {
        return $this->methodIsEnabled(PollingMethodType::Ipmi);
    }

    public function ipmiIsAvailable(): bool
    {
        return $this->methodIsAvailable(PollingMethodType::Ipmi);
    }

    public function icmpIsEnabled(): bool
    {
        return $this->methodIsEnabled(PollingMethodType::Icmp);
    }

    public function icmpIsAvailable(): bool
    {
        return $this->methodIsAvailable(PollingMethodType::Icmp);
    }

    public function unixAgentIsEnabled(): bool
    {
        return $this->methodIsEnabled(PollingMethodType::UnixAgent);
    }

    public function unixAgentIsAvailable(): bool
    {
        return $this->methodIsAvailable(PollingMethodType::UnixAgent);
    }
}
