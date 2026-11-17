<?php

/**
 * SubnetLookup.php
 *
 * Find what surrounds a host: the other interfaces on its subnet and its gateways
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

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Search\Data\Gateway;
use App\Http\Controllers\Search\Data\HostRecord;
use App\Http\Controllers\Search\Data\HostSubnet;
use App\Http\Controllers\Search\Data\NeighborEntry;
use App\Http\Controllers\Search\Data\SubnetAddress;
use App\Models\Ipv4Address;
use App\Models\Ipv6Address;
use App\Models\Port;
use App\Models\Route;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use LibreNMS\Util\IP;
use LibreNMS\Util\IPv4;
use LibreNMS\Util\IPv6;

class SubnetLookup
{
    public function __construct(private readonly User $user)
    {
    }

    public function find(HostRecord $host, ?IP $searchedIp = null): HostSubnet
    {
        $hostIps = collect($searchedIp === null ? $host->ips : [$searchedIp]);
        $ips = $hostIps
            ->reject(fn (IP $ip): bool => $ip->isLinkLocal()) // every interface has a link local subnet
            ->take(3)
            ->values();
        $containing = $ips->mapWithKeys(fn (IP $ip): array => [$ip->compressed() => $this->subnetsContaining($ip)]);

        $sameSubnet = $this->sameSubnet($host, $ips, $containing);
        // any device on the subnet can tell, not just those that have seen this host
        $shared = $containing->flatMap(fn (array $addresses): array => $addresses)->concat($this->addressesWithIps($hostIps))->values();
        $gateways = $this->gateways($host, $shared);

        // the gateways are listed on their own, don't repeat them with the rest of the subnet
        $hops = $gateways->map(fn (Gateway $gateway): string => $gateway->ip->compressed());

        return new HostSubnet(
            $gateways,
            $sameSubnet->reject(fn (SubnetAddress $address): bool => $hops->contains($address->ip->compressed()))->values(),
            $sameSubnet->first()?->network(),
        );
    }

    /**
     * The interface addresses configured with these IPs
     *
     * @param  Collection<int, IP>  $ips
     * @return Collection<int, SubnetAddress>
     */
    public function addressesWithIps(Collection $ips): Collection
    {
        $ipv4 = Ipv4Address::hasAccess($this->user)->with('port.device')
            ->whereIn('ipv4_address', $ips->whereInstanceOf(IPv4::class)->map(fn (IP $ip): string => $ip->compressed())->values())->get();
        $ipv6 = Ipv6Address::hasAccess($this->user)->with('port.device')
            ->whereIn('ipv6_address', $ips->whereInstanceOf(IPv6::class)->map(fn (IP $ip): string => $ip->uncompressed())->values())->get();

        return $ipv4->toBase()->concat($ipv6)->map(SubnetAddress::from(...))->filter()->values();
    }

    /**
     * Ports configured with these IPs, keyed by compressed IP
     *
     * @param  Collection<int, IP>  $ips
     * @return Collection<string, Port>
     */
    public function portsWithIps(Collection $ips): Collection
    {
        $ports = new Collection;
        foreach ($this->addressesWithIps($ips) as $address) {
            if ($address->port) {
                $ports->put($address->ip->compressed(), $address->port);
            }
        }

        return $ports;
    }

    /**
     * Interfaces on the host's subnet: each interface that has the IP in its ARP or ND table, or when
     * none does, those in the most specific subnet containing it. Any host on the subnet can show up here,
     * so this can't tell which is the gateway. Network devices are listed first as they are the most likely.
     *
     * @param  Collection<int, IP>  $ips
     * @param  Collection<array-key, list<SubnetAddress>>  $containing  subnetsContaining() of each IP
     * @return Collection<int, SubnetAddress>
     */
    private function sameSubnet(HostRecord $host, Collection $ips, Collection $containing): Collection
    {
        return $ips
            ->flatMap(function (IP $ip) use ($host, $containing): array {
                $portIds = collect($host->entries)
                    ->filter(fn (NeighborEntry $entry): bool => $entry->ip->compressed() === $ip->compressed())
                    ->map(fn (NeighborEntry $entry): ?int => $entry->port?->port_id)
                    ->filter()->unique()->values()->all();

                return $this->closestAddressOnPorts($ip, $portIds) ?: $containing->get($ip->compressed(), []);
            })
            ->unique(fn (SubnetAddress $address): string => $address->portId . '|' . $address->ip->compressed())
            ->sortBy(fn (SubnetAddress $address): int => $address->isOnNetworkDevice() ? 0 : 1)
            ->values();
    }

    /**
     * The host's gateways: where it and devices on its subnet send their default route. A default route may leave
     * through another of the device's interfaces, so only next hops on the subnet shared with the host count.
     * When no device says, the routers on the subnet are the gateways.
     *
     * @param  Collection<int, SubnetAddress>  $shared
     * @return Collection<int, Gateway>
     */
    private function gateways(HostRecord $host, Collection $shared): Collection
    {
        $hostIps = collect($host->ips)->map(fn (IP $ip): string => $ip->compressed());
        $hostDeviceId = $host->device?->device_id;

        $routes = Route::hasAccess($this->user)
            ->whereIntegerInRaw('device_id', array_filter([...$this->deviceIds($shared), $hostDeviceId]))
            ->whereIn('inetCidrRouteDest', ['0.0.0.0', '::', '0000:0000:0000:0000:0000:0000:0000:0000'])
            ->where('inetCidrRoutePfxLen', 0)
            ->get();

        $nextHops = [];
        foreach ($routes as $route) {
            $hop = IP::parse((string) $route->inetCidrRouteNextHop, true);
            if ($hop === null || $hostIps->contains($hop->compressed())) {
                continue;
            }
            if ($shared->contains(fn (SubnetAddress $address): bool => ($address->port?->device_id == $route->device_id || $route->device_id == $hostDeviceId) && $this->isNextHopOn($hop, $route, $address))) {
                $nextHops[$hop->compressed()]['ip'] = $hop;
                $nextHops[$hop->compressed()]['devices'][$route->device_id] = true;
            }
        }

        if ($nextHops === []) {
            return $this->routersOn($shared)
                ->reject(fn (SubnetAddress $address): bool => $hostIps->contains($address->ip->compressed()))
                ->map(fn (SubnetAddress $address): Gateway => new Gateway($address->ip, $address->port, 0))
                ->values();
        }

        // a link local address is reused on many links, it can't identify the interface
        $interfaces = $this->portsWithIps(collect($nextHops)->pluck('ip')->reject(fn (IP $hop): bool => $hop->isLinkLocal())->values());

        return collect($nextHops)
            ->map(fn (array $nextHop, string $hop): Gateway => new Gateway($nextHop['ip'], $interfaces->get($hop), count($nextHop['devices'])))
            ->sortByDesc('devices')
            ->values();
    }

    /**
     * Addresses of network devices with the subnet directly connected and routes through other networks.
     * Any host has a connected route for its subnet, so that alone doesn't make it a router.
     *
     * @param  Collection<int, SubnetAddress>  $addresses
     * @return Collection<int, SubnetAddress>
     */
    private function routersOn(Collection $addresses): Collection
    {
        $candidates = $addresses->filter(fn (SubnetAddress $address): bool => $address->isOnNetworkDevice());

        $connected = Route::hasAccess($this->user)
            ->whereIntegerInRaw('device_id', $this->deviceIds($candidates))
            ->whereIn('inetCidrRoutePfxLen', $candidates->pluck('prefixLength')->unique()->values())
            ->select(['device_id', 'inetCidrRouteDest', 'inetCidrRoutePfxLen', 'inetCidrRouteNextHop'])
            ->get()->groupBy('device_id');
        $connectedRouters = $candidates->filter(fn (SubnetAddress $address): bool => collect($connected->get($address->port?->device_id, []))
            ->contains(fn (Route $route): bool => $this->isConnectedRoute($route, $address)));

        // devices may have many routes, but few next hops
        $nextHops = Route::hasAccess($this->user)
            ->whereIntegerInRaw('device_id', $this->deviceIds($connectedRouters))
            ->select(['device_id', 'inetCidrRouteNextHop'])->distinct()
            ->get()->groupBy('device_id');

        return $connectedRouters->filter(fn (SubnetAddress $address): bool => collect($nextHops->get($address->port?->device_id, []))
            ->contains(function (Route $route) use ($address): bool {
                $hop = IP::parse((string) $route->inetCidrRouteNextHop, true);

                return $hop !== null && ! $this->isUnspecified($hop) && ! $hop->inNetwork($address->cidr());
            }))->values();
    }

    /**
     * @param  Collection<int, SubnetAddress>  $addresses
     * @return list<int>
     */
    private function deviceIds(Collection $addresses): array
    {
        return $addresses->map(fn (SubnetAddress $address): ?int => $address->port?->device_id)->filter()->unique()->values()->all();
    }

    private function isConnectedRoute(Route $route, SubnetAddress $address): bool
    {
        $destination = IP::parse((string) $route->inetCidrRouteDest, true);
        $hop = IP::parse((string) $route->inetCidrRouteNextHop, true);

        return $destination !== null && $hop !== null
            && (int) $route->inetCidrRoutePfxLen === $address->prefixLength
            && $destination->compressed() === IP::parse($address->ip->getNetworkAddress($address->prefixLength))->compressed()
            && ($this->isUnspecified($hop) || $hop->compressed() === $address->ip->compressed());
    }

    private function isUnspecified(IP $ip): bool
    {
        return in_array($ip->compressed(), ['0.0.0.0', '::'], true);
    }

    private function isNextHopOn(IP $hop, Route $route, SubnetAddress $address): bool
    {
        if ($hop->compressed() === $address->ip->compressed()) {
            return false; // the device's own address
        }

        // a link local next hop has no network to check, the route's interface is the link it's on
        return $hop->isLinkLocal() ? $route->port_id == $address->portId : $hop->inNetwork($address->cidr());
    }

    /**
     * For each port, choose the address sharing the most leading bits among subnets containing the IP.
     * An ARP or ND entry alone does not mean the interface is on the host's subnet.
     *
     * @param  list<int>  $portIds
     * @return list<SubnetAddress>
     */
    private function closestAddressOnPorts(IP $ip, array $portIds): array
    {
        if ($portIds === []) {
            return [];
        }

        $closest = [];
        foreach ($this->addressesLike($ip)->whereIn('port_id', $portIds)->get() as $row) {
            $address = SubnetAddress::from($row);
            if ($address === null || $address->ip->isLinkLocal() || $address->ip->compressed() === $ip->compressed() || ! $ip->inNetwork($address->cidr())) {
                continue;
            }
            $bits = $this->commonBits($ip, $address->ip);
            if ($bits > ($closest[$address->portId]['bits'] ?? -1)) {
                $closest[$address->portId] = ['bits' => $bits, 'address' => $address];
            }
        }

        return array_values(array_column($closest, 'address'));
    }

    /**
     * The interfaces in the most specific subnet containing the IP
     *
     * @return list<SubnetAddress>
     */
    private function subnetsContaining(IP $ip): array
    {
        $query = $this->addressesLike($ip);
        if ($ip instanceof IPv4) {
            $query->whereBetween('ipv4_prefixlen', [8, 31])
                ->whereRaw('INET_ATON(ipv4_address) >> (32 - ipv4_prefixlen) = INET_ATON(?) >> (32 - ipv4_prefixlen)', [$ip->compressed()])
                ->orderByDesc('ipv4_prefixlen');
        } else {
            // whole hex digits narrow it down, the exact check is below. Shorter prefixes aren't real interface subnets.
            $query->whereBetween('ipv6_prefixlen', [32, 127])
                ->whereRaw('SUBSTR(HEX(INET6_ATON(ipv6_address)), 1, ipv6_prefixlen DIV 4) = SUBSTR(?, 1, ipv6_prefixlen DIV 4)', [strtoupper(bin2hex($ip->packed()))])
                ->orderByDesc('ipv6_prefixlen');
        }

        $inSubnet = $query->limit(50)->get()->toBase()
            ->map(SubnetAddress::from(...))
            ->filter(fn (?SubnetAddress $address): bool => $address !== null && $address->ip->compressed() !== $ip->compressed() && $ip->inNetwork($address->cidr()))
            ->values();

        // ordered by prefix, only the most specific subnet is the host's own
        $longest = $inSubnet->first()?->prefixLength;

        return $inSubnet->filter(fn (SubnetAddress $address): bool => $address->prefixLength === $longest)->values()->all();
    }

    /**
     * Interface addresses of the same family as this IP
     *
     * @return Builder<Ipv4Address>|Builder<Ipv6Address>
     */
    private function addressesLike(IP $ip): Builder
    {
        return ($ip instanceof IPv4 ? Ipv4Address::hasAccess($this->user) : Ipv6Address::hasAccess($this->user))->with('port.device');
    }

    private function commonBits(IP $first, IP $second): int
    {
        $a = $first->packed();
        $b = $second->packed();
        $bits = 0;

        for ($i = 0, $length = min(strlen($a), strlen($b)); $i < $length; $i++) {
            $difference = ord($a[$i]) ^ ord($b[$i]);
            if ($difference !== 0) {
                return $bits + 8 - strlen(decbin($difference));
            }
            $bits += 8;
        }

        return $bits;
    }
}
