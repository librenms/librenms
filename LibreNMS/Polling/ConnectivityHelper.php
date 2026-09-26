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

/**
 * Temporary compatibility shim for legacy code. Delegates to Device::polling().
 */
readonly class ConnectivityHelper
{
    public function __construct(
        private Device $device,
    ) {
    }

    public function isAvailable(): bool
    {
        return $this->device->polling()->failedAvailabilityChecks()->isEmpty();
    }

    public function hasAvailability(): bool
    {
        return $this->device->polling()->hasAvailabilityCheck();
    }

    public function snmpIsEnabled(): bool
    {
        return $this->device->polling()->isEnabled(PollingMethodType::Snmp);
    }

    public function snmpIsAvailable(): bool
    {
        return $this->device->polling()->isAvailable(PollingMethodType::Snmp);
    }

    public function ipmiIsEnabled(): bool
    {
        return $this->device->polling()->isEnabled(PollingMethodType::Ipmi);
    }

    public function ipmiIsAvailable(): bool
    {
        return $this->device->polling()->isAvailable(PollingMethodType::Ipmi);
    }

    public function icmpIsEnabled(): bool
    {
        return $this->device->polling()->isEnabled(PollingMethodType::Icmp);
    }

    public function icmpIsAvailable(): bool
    {
        return $this->device->polling()->isAvailable(PollingMethodType::Icmp);
    }

    public function unixAgentIsEnabled(): bool
    {
        return $this->device->polling()->isEnabled(PollingMethodType::UnixAgent);
    }

    public function unixAgentIsAvailable(): bool
    {
        return $this->device->polling()->isAvailable(PollingMethodType::UnixAgent);
    }
}
