<?php

/**
 * AlcatelMacAddressMib.php
 *
 * Discover FDB data with ALCATEL-IND1-MAC-ADDRESS-MIB
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
 * @link      https://www.librenms.org
 *
 * @copyright LibreNMS contributors
 * @author    Tony Murray <murraytony@gmail.com>
 * @author    JoseUPV
 * @author    Paul Iercosan <mail@paulierco.ro>
 */

namespace LibreNMS\OS\Traits;

use App\Facades\PortCache;
use App\Models\PortsFdb;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Util\Mac;
use SnmpQuery;

trait AlcatelMacAddressMib
{
    /** @var array<int|string, int|string>|null */
    private ?array $alcatelLagPorts = null;

    /**
     * slMacAddressDisposition is indexed by ifIndex, VLAN number and MAC address.
     * This object only exists in the aos6 copy of the MIB and aos7 falls back to it.
     *
     * @return Collection<int, PortsFdb>
     */
    private function alcatelDispositionFdb(): Collection
    {
        return SnmpQuery::mibDir('nokia/aos6')->walk('ALCATEL-IND1-MAC-ADDRESS-MIB::slMacAddressDisposition')
            ->mapTable(fn ($data, $ifIndex, $vlan, $mac) => $this->alcatelFdbEntry($vlan, $mac, (int) $ifIndex))
            ->filter();
    }

    /**
     * @param  array<int|string, int|string>  $bridgePorts  ifIndex keyed by bridge port, when $port is a bridge port.
     *                                                      These overlap with ifIndexes (slot 9 port 1 is bridge port 1025)
     */
    private function alcatelFdbEntry(int|string $vlan, string $mac, int $port, array $bridgePorts = []): ?PortsFdb
    {
        // Map physical LAG members using data discovered by the ports-stack module.
        // Only rows whose parent is a LAG interface are used, other stacked interfaces are not remapped.
        $this->alcatelLagPorts ??= $this->getDevice()->portsStack()
            ->join('ports', 'ports.port_id', '=', 'ports_stack.low_port_id')
            ->where('ports.ifType', 'ieee8023adLag')
            ->where('ifStackStatus', 'active')
            ->where('high_ifIndex', '>', 0)
            ->where('low_ifIndex', '>', 0)
            ->pluck('low_ifIndex', 'high_ifIndex')
            ->all();

        $ifIndex = (int) ($bridgePorts[$port] ?? 0);
        $port_id = $ifIndex ? PortCache::getIdFromIfIndex($this->alcatelLagPorts[$ifIndex] ?? $ifIndex, $this->getDeviceId()) : null;

        // AOS may report an ifIndex directly instead of a bridge port.
        // Aggregate bridge ports start at 4098 and map to 40000001, etc.
        $port_id ??= PortCache::getIdFromIfIndex(match (true) {
            $port >= 40000000 => $port,
            $port >= 4098 && $port < 5000 => 40000000 + ($port - 4097),
            default => $this->alcatelLagPorts[$port] ?? $port,
        }, $this->getDeviceId());

        if (! $port_id) {
            Log::debug("No port mapping for port $port");

            return null;
        }

        return new PortsFdb([
            'port_id' => $port_id,
            'mac_address' => Mac::parse($mac)->hex(),
            'vlan' => (int) $vlan ?: null,
        ]);
    }
}
