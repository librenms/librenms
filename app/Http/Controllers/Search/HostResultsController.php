<?php

/**
 * HostResultsController.php
 *
 * Network search results: one host as a summary, anything else as a list of hosts
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

use App\Facades\LibrenmsConfig;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Search\Data\HostKey;
use App\Http\Controllers\Search\Data\HostSubnet;
use App\Http\Requests\HostSearchRequest;
use App\Models\Port;
use Illuminate\Http\JsonResponse;
use LibreNMS\Util\Dns;
use LibreNMS\Util\IP;
use LibreNMS\Util\IPv4;
use LibreNMS\Util\Mac;
use LibreNMS\Util\SearchTerm;

class HostResultsController extends Controller
{
    public function __invoke(HostSearchRequest $request, Dns $dns): JsonResponse
    {
        $this->authorize('viewAny', Port::class);

        $terms = $request->terms();

        // filters narrow a list, opening a host from that list asks for it directly
        if (count($terms) == 1 && ($request->filters() === [] || $request->validated('view') !== null)) {
            $response = $this->hostResponse($request, $terms[0], $dns);
            if ($response !== null) {
                return $response;
            }
        }

        return $this->listResponse($request);
    }

    /**
     * The summary of the host a term names, null when the term should be listed instead
     */
    private function hostResponse(HostSearchRequest $request, SearchTerm $term, Dns $dns): ?JsonResponse
    {
        $target = $this->hostTarget($term, $dns);
        if ($target === null) {
            return null;
        }

        $host = (new HostLookup($request->user()))->find([$target], follow: true)[0];

        $subnet = (new SubnetLookup($request->user()))->find($host);

        if ($host->isNotSeen()) {
            // not a host, but it may be the start of an IPv6 address, or the name or serial number of a device
            $mayBeListed = $term->mayBePartialIpv6()
                || (($target->hostname !== null || $term->isBareHex()) && $request->listQuery()->build()->exists());

            return $mayBeListed ? null : $this->notFoundResponse($request, $term, $subnet, $target->hostname ? $host->ips : []);
        }

        $name = $target->hostname ?? collect($host->ips)->take(3)
            ->map(fn (IP $ip) => $dns->resolveHostname($ip->compressed()))
            ->filter()->first();

        return response()->json(['mode' => 'host', 'html' => view('search.host', ['host' => $host, 'name' => $name, 'subnet' => $subnet])->render()]);
    }

    /**
     * The host a search names: an IP, a MAC, or the IPs of a hostname
     */
    private function hostTarget(SearchTerm $term, Dns $dns): ?HostKey
    {
        if ($term->isExactIp()) {
            return new HostKey(ips: [$term->ip]);
        }

        if ($term->isFullMac()) {
            return new HostKey(macs: [$term->macHex]);
        }

        if ($term->isHostname()) {
            $hostname = rtrim($term->value, '.');
            $ips = [];
            foreach ($dns->getAddresses($hostname) as $address) {
                if ($ip = IP::parse($address, true)) {
                    $ips[$ip->compressed()] = $ip;
                }
            }

            return $ips ? new HostKey(ips: array_values($ips), hostname: $hostname) : null;
        }

        return null;
    }

    private function listResponse(HostSearchRequest $request): JsonResponse
    {
        $query = $request->listQuery();
        $sort = $request->validated('sort') ?? $query->defaultSort();
        $direction = $request->validated('dir') ?? 'asc';
        $results = $query->build($sort, $direction)
            ->paginate((int) ($request->validated('per_page') ?? 25))
            ->withPath(route('search'))
            ->appends($request->except('page'));

        if ($results->total() == 0) {
            return $this->notFoundResponse($request, $request->terms()[0] ?? null);
        }

        $keys = [];
        foreach ($results->items() as $row) {
            $keys[] = $query->hostKey((array) $row);
        }

        return response()->json([
            'mode' => 'list',
            'total' => $results->total(),
            'sort' => $sort,
            'dir' => $direction,
            'summary' => $this->summary($request, $query, $results->total()),
            'html' => view('search.list', [
                'hosts' => (new HostLookup($request->user()))->find($keys),
                'results' => $results,
                'sort' => $sort,
                'direction' => $direction,
            ])->render(),
        ]);
    }

    private function summary(HostSearchRequest $request, HostListQuery $query, int $total): string
    {
        $terms = $request->terms();
        $network = count($terms) == 1 && $request->filters() === [] && $terms[0]->isNetwork ? $terms[0]->ip : null;
        if ($network instanceof IPv4) {
            $parts = [__('search.list.network_seen', ['count' => $total, 'size' => number_format($network->addressCount())])];
        } else {
            $parts = [$query->groupsByMac() ? trans_choice('search.macs_count', $total) : trans_choice('search.list.hosts', $total)];
        }

        if (count($terms) > 1 && ($notSeen = $query->notSeenCount($total)) > 0) {
            $parts[] = __('search.list.not_seen', ['count' => $notSeen]);
        }
        if ($request->termCount() > SearchTerm::MAX_TERMS) {
            $parts[] = __('search.list.terms_ignored', ['count' => SearchTerm::MAX_TERMS, 'total' => $request->termCount()]);
        }

        return implode(' · ', $parts);
    }

    /**
     * Nothing matched: say what was searched and offer the searches most likely to help
     *
     * @param  list<IP>  $resolved  what a searched hostname resolved to
     */
    private function notFoundResponse(HostSearchRequest $request, ?SearchTerm $term, ?HostSubnet $subnet = null, array $resolved = []): JsonResponse
    {
        $mac = $term?->isFullMac() ? Mac::parse((string) $term->macHex) : null;

        return response()->json([
            'mode' => 'not_found',
            'total' => 0,
            'html' => view('search.not-found', [
                'query' => trim($request->validated('q') ?? ''),
                'resolved' => $resolved,
                'hasFilters' => $request->filters() !== [],
                'subnet' => $subnet,
                'network' => $term?->ip !== null ? $subnet?->network : null,
                'ouiPrefix' => $mac ? implode(':', array_slice($mac->array(), 0, 3)) : null,
                'vendor' => $mac && LibrenmsConfig::get('mac_oui.enabled') ? $mac->vendor() : null,
            ])->render(),
        ]);
    }
}
