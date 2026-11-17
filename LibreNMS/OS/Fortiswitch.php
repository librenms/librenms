<?php

/**
 * Fortiswitch.php
 *
 * Fortinet has special needs:
 *
 * # diagnose switch mac-address list | grep 00:07:5f:9c:41:af
 * MAC: 00:07:5f:9c:41:af  VLAN: 128 Trunk: 8FFTF24008005-0(trunk-id 13)
 *
 * BRIDGE-MIB::dot1dTpFdbAddress.2 = STRING: 0:7:5f:9c:41:af
 * BRIDGE-MIB::dot1dTpFdbPort.2 = INTEGER: 32781
 * BRIDGE-MIB::dot1dBasePort.37 = INTEGER: 32781
 * BRIDGE-MIB::dot1dBasePortIfIndex.37 = INTEGER: 87
 * IF-MIB::ifName.87 = STRING: 8FFTF24008005-0
 *
 * MAC on index 2 is on dot1dTpFdbPort 32781. 32781 is dot1dBasePort index 37.
 * dot1dBasePortIfIndex index 37 is interface 87.
 *
 * Fortinet doesn't give us the VLAN ID. Bug report in 9239914 & 10430993
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
 * @copyright  2025 CTNET BV
 * @author     Rudy Broersma <r.broersma@ctnet.nl>
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use LibreNMS\OS;
use SnmpQuery;

class Fortiswitch extends OS
{
    /** @var array<int|string, int|string>|null dot1dBasePort index keyed by its value */
    private ?array $basePortIndexes = null;

    protected function fdbPortId(int|string $bridgePort, int|string $vlan): ?int
    {
        $this->basePortIndexes ??= array_flip(SnmpQuery::walk('BRIDGE-MIB::dot1dBasePort')->pluck());
        $basePort = $this->basePortIndexes[$bridgePort] ?? $bridgePort;

        return PortCache::getIdFromIfIndex($this->bridgePortIfIndexes()[$basePort] ?? null, $this->getDeviceId());
    }
}
