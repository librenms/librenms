<?php

/*
 * ConnectivityHelper.php
 *
 * Helper to check the connectivity to a device and optionally save metrics about that connectivity
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

use App\Facades\LibrenmsConfig;
use App\Models\Device;

readonly class ConnectivityHelper
{
    public function __construct(
        private Device $device,
    ) {
    }

    public function isAvailable(): bool
    {
        return $this->device->status;
    }

    public function hasAvailability(): bool
    {
        return $this->icmpIsEnabled() || $this->snmpIsAvailable();
    }

    public function snmpIsEnabled(): bool
    {
        return $this->device->snmp_disable === false;
    }

    public function icmpIsEnabled(): bool
    {
        return LibrenmsConfig::get('icmp_check') && ! ($this->device->exists && $this->device->getAttrib('override_icmp_disable') === 'true');
    }

    public function snmpIsAvailable(): bool
    {
        return $this->snmpIsEnabled() && $this->isAvailable() && ! str_contains($this->device->status_reason, 'snmp');
    }

    public function icmpIsAvailable(): bool
    {
        return $this->icmpIsEnabled() && $this->isAvailable() && ! str_contains($this->device->status_reason, 'icmp');
    }

    public function ipmiIsEnabled(): bool
    {
        return $this->device->exists && $this->device->getAttrib('ipmi_hostname');
    }

    public function ipmiIsAvailable(): bool
    {
        return $this->ipmiIsEnabled();
    }

    public function unixAgentIsEnabled(): bool
    {
        return false;
    }

    public function unixAgentIsAvailable(): bool
    {
        return $this->unixAgentIsEnabled();
    }
}
