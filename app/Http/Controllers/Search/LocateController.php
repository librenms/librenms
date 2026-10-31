<?php

/**
 * LocateController.php
 *
 * Summarize where a single host (IP, MAC or hostname) was seen on the network
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

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Ipv4Mac;
use App\Models\Ipv6Address;
use App\Models\Ipv6Nd;
use App\Models\Port;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use LibreNMS\Util\Dns;
use LibreNMS\Util\IP;
use LibreNMS\Util\IPv4;
use LibreNMS\Util\IPv6;
use LibreNMS\Util\Mac;
use LibreNMS\Util\SearchTerm;

class LocateController extends Controller
{
    use CorrelatesMacs;

    public function __invoke(Request $request, Dns $dns): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|max:255',
        ]);

        /** @var User $user */
        $user = $request->user();
        abort_unless($user->can('viewAny', Port::class), 403);

        $term = SearchTerm::parse($validated['q']);

        $target = $this->target($term, $dns);
        if ($target === null) {
            return response()->json(['html' => '']);
        }
        [$ips, $macs, $hostname] = [collect($target[0]), collect($target[1]), $target[2]];

        $bindings = $this->bindings($ips, $macs, $user);
        $macs = $macs->merge($bindings->pluck('mac'))->unique()->values();
        $ips = $ips->merge($bindings->pluck('ip'))->unique(fn (IP $ip) => $ip->compressed())->values();

        return response()->json([
            'html' => view('search.locate', [
                'term' => $term,
                'hostname' => $hostname,
                'ips' => $ips,
                'macs' => $macs->mapWithKeys(fn ($mac) => [$mac => Mac::parse($mac)]),
                'dnsNames' => $hostname ? collect() : $ips->take(3)->map(fn (IP $ip) => $dns->resolveHostname($ip->compressed()))->filter()->unique()->values(),
                'devices' => $this->monitoredDevices($ips, $macs, $hostname, $user),
                'owners' => $this->portsWithMac($macs, $user)->flatten(1),
                'locations' => $this->fdbLocations($macs, $user),
                'bindings' => $bindings,
                'subnets' => $this->subnets($ips, $user),
            ])->render(),
        ]);
    }

    /**
     * The host to locate: an IP, a MAC, or the IPs a hostname resolves to
     *
     * @return array{list<IP>, list<string>, string|null}|null
     */
    private function target(SearchTerm $term, Dns $dns): ?array
    {
        if ($term->ip !== null && ! $term->isNetwork) {
            return [[$term->ip], [], null];
        }

        if ($term->macHex !== null && $term->isFullMac()) {
            return [[], [$term->macHex], null];
        }

        if ($term->isHostname()) {
            $hostname = rtrim($term->value, '.');
            $ips = [];
            foreach ($dns->getAddresses($hostname) as $address) {
                if ($ip = IP::parse($address, true)) {
                    $ips[$ip->compressed()] = $ip;
                }
            }

            return empty($ips) ? null : [array_values($ips), [], $hostname];
        }

        return null;
    }

    /**
     * ARP and ND entries for the IPs or MACs, these were learned by the routers in front of the host
     *
     * @param  Collection<int, IP>  $ips
     * @param  Collection<int, string>  $macs
     * @return Collection<int, array{ip: IP, mac: string, port: Port|null}>
     */
    private function bindings(Collection $ips, Collection $macs, User $user): Collection
    {
        $ipv4 = $ips->whereInstanceOf(IPv4::class)->map(fn (IP $ip) => $ip->compressed());
        $ipv6 = $ips->whereInstanceOf(IPv6::class)->map(fn (IP $ip) => $ip->uncompressed());

        $arp = Ipv4Mac::hasAccess($user)->with('port.device')
            ->where(fn (Builder $q) => $q->whereIn('ipv4_address', $ipv4)->orWhereIn('mac_address', $macs))
            ->limit(50)->get()
            ->map(fn (Ipv4Mac $entry): array => ['ip' => IP::parse($entry->ipv4_address, true), 'mac' => $entry->mac_address, 'port' => $entry->port]);

        $nd = Ipv6Nd::hasAccess($user)->with('port.device')
            ->where(fn (Builder $q) => $q->whereIn('ipv6_address', $ipv6)->orWhereIn('mac_address', $macs))
            ->limit(50)->get()
            ->map(fn (Ipv6Nd $entry): array => ['ip' => IP::parse($entry->ipv6_address, true), 'mac' => $entry->mac_address, 'port' => $entry->port]);

        return $arp->toBase()->concat($nd)->filter(fn (array $binding): bool => $binding['ip'] !== null)->values();
    }

    /**
     * @param  Collection<int, IP>  $ips
     * @param  Collection<int, string>  $macs
     * @return Collection<int, Device>
     */
    private function monitoredDevices(Collection $ips, Collection $macs, ?string $hostname, User $user): Collection
    {
        $ipv4 = $ips->whereInstanceOf(IPv4::class)->map(fn (IP $ip) => $ip->compressed());
        $ipv6 = $ips->whereInstanceOf(IPv6::class)->map(fn (IP $ip) => $ip->uncompressed());

        return Device::hasAccess($user)
            ->where(function (Builder $query) use ($ips, $ipv4, $ipv6, $macs, $hostname): void {
                $query->whereIn('ip', $ips->map(fn (IP $ip) => $ip->packed()))
                    ->orWhereIn('hostname', $ips->map(fn (IP $ip) => $ip->compressed())->when($hostname, fn ($names) => $names->push($hostname)))
                    ->orWhereHas('ports', fn (Builder $q) => $q->whereIn('ifPhysAddress', $macs))
                    ->orWhereHas('ports.ipv4', fn (Builder $q) => $q->whereIn('ipv4_address', $ipv4))
                    ->orWhereHas('ports.ipv6', fn (Builder $q) => $q->whereIn('ipv6_address', $ipv6));
            })
            ->limit(10)
            ->get();
    }

    /**
     * Interfaces configured in the same subnet as the IPs, these are the likely gateways
     *
     * @param  Collection<int, IP>  $ips
     * @return Collection<int, Ipv4Address|Ipv6Address>
     */
    private function subnets(Collection $ips, User $user): Collection
    {
        $own = $ips->map(fn (IP $ip) => $ip->compressed());

        return $ips->take(3)
            ->flatMap(fn (IP $ip) => $this->subnetsContaining($ip, $user))
            ->reject(fn (Ipv4Address|Ipv6Address $address): bool => $own->contains(IP::parse($address instanceof Ipv4Address ? $address->ipv4_address : $address->ipv6_address, true)?->compressed()))
            ->values();
    }

    /**
     * @return array<int, Ipv4Address|Ipv6Address>
     */
    private function subnetsContaining(IP $ip, User $user): array
    {
        if ($ip instanceof IPv4) {
            return Ipv4Address::hasAccess($user)->with('port.device')
                ->whereBetween('ipv4_prefixlen', [8, 31])
                ->whereRaw('INET_ATON(ipv4_address) >> (32 - ipv4_prefixlen) = INET_ATON(?) >> (32 - ipv4_prefixlen)', [$ip->compressed()])
                ->limit(10)->get()->all();
        }

        if ($ip->isLinkLocal()) {
            return []; // every interface has a link local subnet
        }

        // compare whole hex digits of the prefix, close enough for the common /64 and /48
        return Ipv6Address::hasAccess($user)->with('port.device')
            ->whereBetween('ipv6_prefixlen', [16, 127])
            ->whereRaw('LEFT(HEX(INET6_ATON(ipv6_address)), ipv6_prefixlen DIV 4) = LEFT(?, ipv6_prefixlen DIV 4)', [strtoupper(bin2hex($ip->packed()))])
            ->limit(10)->get()->all();
    }
}
