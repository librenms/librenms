<?php

/**
 * Liebert.php
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
 * @copyright  2026 LibreNMS contributors
 * @author     Andrei-Alexandru Bleortu
 */

namespace LibreNMS\OS;

use Illuminate\Support\Str;
use LibreNMS\Interfaces\Discovery\DeviceTypeDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Liebert extends OS implements DeviceTypeDiscovery
{
    // The same agent card serves UPSes, power distribution and cooling units, and
    // lgpAgentDeviceId names the registration branch of the device it manages:
    // lgpAcProducts and lgpAcPACCProducts are the environmental families.
    private const COOLING_PRODUCTS = [
        '.1.3.6.1.4.1.476.1.42.4.3.',
        '.1.3.6.1.4.1.476.1.42.4.12.',
    ];

    private ?bool $coolingUnit = null;

    public function isCoolingUnit(): bool
    {
        return $this->coolingUnit ??= Str::startsWith(
            SnmpQuery::numeric()->get('LIEBERT-GP-AGENT-MIB::lgpAgentDeviceId.1')->value(),
            self::COOLING_PRODUCTS
        );
    }

    public function discoverDeviceType(): ?string
    {
        return $this->isCoolingUnit() ? 'environment' : null;
    }
}
