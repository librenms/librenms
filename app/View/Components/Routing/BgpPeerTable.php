<?php

/**
 * BgpPeerTable.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 LibreNMS
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\View\Components\Routing;

use App\Models\BgpPeer;
use App\Models\BgpPeerCbgp;
use App\Models\Ipv4Address;
use App\Models\Ipv6Address;
use App\Models\Port;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\Component;
use LibreNMS\Util\IP;
use LibreNMS\Util\Number;
use LibreNMS\Util\Rewrite;
use LibreNMS\Util\Time;

/**
 * @phpstan-type PeerRow array{
 *     peer: BgpPeer,
 *     local_address: string,
 *     local_port: Port|null,
 *     identifier: string,
 *     linked_port: Port|null,
 *     vrf: string,
 *     peer_type: string,
 *     peer_type_class: string,
 *     afi_list: string,
 *     admin_color: string,
 *     state_color: string,
 *     last_error: string,
 *     uptime: string,
 *     in_updates: string,
 *     out_updates: string,
 *     prefixes: list<array{family: string, accepted: int, limit: int, percent: int|null, class: string}>,
 *     graph_type: string|null,
 *     graph_id: int,
 * }
 */
class BgpPeerTable extends Component
{
    public const GRAPHS = [
        'updates',
        'prefixes_ipv4unicast',
        'prefixes_ipv4multicast',
        'prefixes_ipv4vpn',
        'prefixes_ipv6unicast',
        'prefixes_ipv6multicast',
        'prefixes_ipv6vpn',
        'macaccounting_bits',
        'macaccounting_pkts',
    ];

    /** PrefixAdminLimit is 0, or the maximum of an Unsigned32 (IOS-XR), when no limit is configured */
    private const int NO_PREFIX_LIMIT = 4294967295;

    /** @var list<PeerRow> */
    public array $rows;
    public int $columnCount;
    public bool $showVrf;
    public bool $showPrefixes;

    /**
     * @param  EloquentCollection<int, BgpPeer>  $peers
     * @param  string|null  $graph  one of self::GRAPHS to show a graph under each peer
     */
    public function __construct(
        EloquentCollection $peers,
        public readonly ?string $graph = null,
        public readonly bool $showDevice = false,
    ) {
        $peers->loadMissing(['device', 'vrf']);
        $peers = self::filterForGraph($peers, $graph);

        $cbgp = $this->loadCbgp($peers);
        $linkedPorts = $this->resolveLinkedPorts($peers);
        $localPorts = $this->resolveLocalPorts($peers);
        $macAccountingIds = str_starts_with((string) $graph, 'macaccounting_') ? self::macAccountingIds($peers) : [];

        $this->rows = $peers->map(fn (BgpPeer $peer) => $this->formatPeer(
            $peer,
            $cbgp->get(self::cbgpKey($peer->device_id, $peer->bgpPeerIdentifier, $peer->context_name), collect()),
            $linkedPorts[$peer->device_id . '|' . $peer->bgpPeerIdentifier] ?? null,
            $localPorts[$peer->device_id . '|' . $peer->bgpPeerIface] ?? null,
            $macAccountingIds[$peer->device_id . '|' . $peer->bgpPeerIdentifier] ?? null,
        ))->values()->all();

        // only show the columns when a peer has something to show in them
        $this->showVrf = collect($this->rows)->contains(fn (array $row) => $row['vrf'] !== '');
        $this->showPrefixes = collect($this->rows)->contains(fn (array $row) => ! empty($row['prefixes']));
        $this->columnCount = 9 + (int) $this->showVrf + (int) $this->showPrefixes;
    }

    public function render(): View|Closure|string
    {
        return view('components.routing.bgp-peer-table');
    }

    /**
     * Unicast prefix graphs only make sense for peers of the matching address family
     *
     * @param  EloquentCollection<int, BgpPeer>  $peers
     * @return EloquentCollection<int, BgpPeer>
     */
    public static function filterForGraph(EloquentCollection $peers, ?string $graph): EloquentCollection
    {
        return match ($graph) {
            'prefixes_ipv4unicast' => $peers->reject(fn (BgpPeer $p) => str_contains($p->bgpPeerIdentifier, ':')),
            'prefixes_ipv6unicast' => $peers->filter(fn (BgpPeer $p) => str_contains($p->bgpPeerIdentifier, ':')),
            default => $peers,
        };
    }

    /**
     * Mac accounting entries for IPv4 peers, keyed by device_id|peer ip
     *
     * @param  Collection<int, BgpPeer>  $peers
     * @return array<string, int>
     */
    public static function macAccountingIds(Collection $peers): array
    {
        $ipv4Peers = $peers->reject(fn (BgpPeer $p) => str_contains($p->bgpPeerIdentifier, ':'));

        if ($ipv4Peers->isEmpty()) {
            return [];
        }

        return DB::table('ipv4_mac')
            ->join('mac_accounting', 'mac_accounting.mac', '=', 'ipv4_mac.mac_address')
            ->join('ports', 'ports.port_id', '=', 'mac_accounting.port_id')
            ->whereIn('ports.device_id', $ipv4Peers->pluck('device_id')->unique())
            ->whereIn('ipv4_mac.ipv4_address', $ipv4Peers->pluck('bgpPeerIdentifier')->unique())
            ->get(['ports.device_id', 'ipv4_mac.ipv4_address', 'mac_accounting.ma_id'])
            ->mapWithKeys(fn ($row) => [$row->device_id . '|' . $row->ipv4_address => (int) $row->ma_id])
            ->all();
    }

    /**
     * @param  Collection<int, BgpPeerCbgp>  $cbgp
     * @return PeerRow
     */
    private function formatPeer(BgpPeer $peer, Collection $cbgp, ?Port $linkedPort, ?Port $localPort, ?int $macAccountingId): array
    {
        [$peerType, $peerTypeClass] = $this->determinePeerType($peer->bgpPeerRemoteAs, $peer->device?->bgpLocalAs);
        $afiSafis = $cbgp->map(fn (BgpPeerCbgp $c) => $c->afi . $c->safi)->all();
        [$graphType, $graphId] = $this->resolveGraph($peer, $afiSafis, $macAccountingId);

        return [
            'peer' => $peer,
            'local_address' => $this->formatLocalAddress($peer),
            'local_port' => $localPort,
            'identifier' => IP::parse($peer->bgpPeerIdentifier, true)?->compressed() ?: $peer->bgpPeerIdentifier,
            'linked_port' => $linkedPort,
            'vrf' => (string) ($peer->vrf?->vrf_name ?: $peer->context_name),
            'peer_type' => $peerType,
            'peer_type_class' => $peerTypeClass,
            'afi_list' => $cbgp->map(fn (BgpPeerCbgp $c) => "$c->afi.$c->safi")->implode(', '),
            'admin_color' => in_array($peer->bgpPeerAdminStatus, ['start', 'running'], true) ? 'success' : 'default',
            'state_color' => $peer->bgpPeerState === 'established' ? 'success' : 'danger',
            'last_error' => $this->formatLastError($peer),
            'uptime' => Time::formatInterval($peer->bgpPeerFsmEstablishedTime),
            'in_updates' => Number::formatSi($peer->bgpPeerInUpdates, 2, 0, ''),
            'out_updates' => Number::formatSi($peer->bgpPeerOutUpdates, 2, 0, ''),
            'prefixes' => $cbgp->map(fn (BgpPeerCbgp $c) => $this->formatPrefixes($c))->values()->all(),
            'graph_type' => $graphType,
            'graph_id' => $graphId,
        ];
    }

    /**
     * @param  Collection<int, BgpPeer>  $peers
     * @return Collection<array-key, EloquentCollection<int, BgpPeerCbgp>>
     */
    private function loadCbgp(Collection $peers): Collection
    {
        if ($peers->isEmpty()) {
            return new Collection;
        }

        return BgpPeerCbgp::query()
            ->whereIn('device_id', $peers->pluck('device_id')->unique())
            ->get()
            ->groupBy(fn (BgpPeerCbgp $c) => self::cbgpKey($c->device_id, $c->bgpPeerIdentifier, $c->context_name))
            ->toBase();
    }

    /**
     * Address families are stored in the snmp context of their session, the same address may be a session in another context
     */
    private static function cbgpKey(int|string|null $deviceId, string $identifier, ?string $context): string
    {
        return "$deviceId|$identifier|$context";
    }

    /**
     * The local interfaces the sessions are on, keyed by device_id|ifIndex
     *
     * @param  Collection<int, BgpPeer>  $peers
     * @return array<string, Port>
     */
    private function resolveLocalPorts(Collection $peers): array
    {
        $peers = $peers->filter(fn (BgpPeer $peer) => ! empty($peer->bgpPeerIface));

        if ($peers->isEmpty()) {
            return [];
        }

        return Port::whereIn('device_id', $peers->pluck('device_id')->unique())
            ->whereIn('ifIndex', $peers->pluck('bgpPeerIface')->unique())
            ->get()
            ->keyBy(fn (Port $port) => "$port->device_id|$port->ifIndex")
            ->all();
    }

    private function formatLocalAddress(BgpPeer $peer): string
    {
        $ip = IP::parse($peer->bgpLocalAddr, true);

        return $ip === null || in_array($ip->compressed(), ['0.0.0.0', '::'], true) ? '' : $ip->compressed();
    }

    /**
     * Accepted prefixes and the maximum-prefix limit of an address family.
     * Colored by the warning threshold and limit configured on the device.
     *
     * @return array{family: string, accepted: int, limit: int, percent: int|null, class: string}
     */
    private function formatPrefixes(BgpPeerCbgp $cbgp): array
    {
        $accepted = (int) $cbgp->AcceptedPrefixes;
        $limit = (int) $cbgp->PrefixAdminLimit >= self::NO_PREFIX_LIMIT ? 0 : (int) $cbgp->PrefixAdminLimit;
        $threshold = (int) $cbgp->PrefixThreshold;
        // compare the unrounded ratio, the percentage is rounded for display only
        $ratio = $limit > 0 ? $accepted / $limit * 100 : null;
        $percent = $ratio === null ? null : (int) round($ratio);

        return [
            'family' => "$cbgp->afi.$cbgp->safi",
            'accepted' => $accepted,
            'limit' => $limit,
            'percent' => $percent,
            'class' => match (true) {
                $ratio === null => '',
                $accepted >= $limit => 'text-danger',
                $threshold > 0 && $ratio >= $threshold => 'text-warning',
                default => '',
            },
        ];
    }

    /**
     * Find the remote ports with the peer addresses, keyed by device_id|bgpPeerIdentifier
     *
     * @param  Collection<int, BgpPeer>  $peers
     * @return array<string, Port>
     */
    private function resolveLinkedPorts(Collection $peers): array
    {
        // unnumbered peers use the interface name as identifier
        $addresses = $peers->mapWithKeys(fn (BgpPeer $peer) => [
            "$peer->device_id|$peer->bgpPeerIdentifier" => (IP::parse($peer->bgpPeerIdentifier, true) ?? IP::parse($peer->bgpPeerRemoteAddr ?? '', true))?->uncompressed(),
        ])->filter();

        $ips = Ipv4Address::whereIn('ipv4_address', $addresses)->has('port')->with('port.device')->get()->groupBy('ipv4_address')->toBase()
            ->merge(Ipv6Address::whereIn('ipv6_address', $addresses)->has('port')->with('port.device')->get()->groupBy('ipv6_address'));

        // addresses may not be unique (link-local, VRFs)
        return $addresses->map(fn (string $address) => $ips->get($address)?->containsOneItem() ? $ips[$address]->first()->port : null)
            ->filter()->all();
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function determinePeerType(int|string|null $remoteAs, int|string|null $localAs): array
    {
        if ($remoteAs == $localAs) {
            return ['iBGP', 'text-primary'];
        }

        $as = (int) $remoteAs;
        $isPrivate = ($as >= 64512 && $as <= 65534) || ($as >= 4200000000 && $as <= 4294967294);

        return $isPrivate ? ['Priv eBGP', 'text-info'] : ['eBGP', 'text-success'];
    }

    private function formatLastError(BgpPeer $peer): string
    {
        $code = $peer->bgpPeerLastErrorCode ?? 0;
        $subcode = $peer->bgpPeerLastErrorSubCode ?? 0;
        $error = ($code || $subcode) ? Rewrite::bgpErrorCode($code, $subcode) : '';

        return trim("$error {$peer->bgpPeerLastErrorText}");
    }

    /**
     * @param  string[]  $afiSafis
     * @return array{0: string|null, 1: int}
     */
    private function resolveGraph(BgpPeer $peer, array $afiSafis, ?int $macAccountingId): array
    {
        if ($this->graph === 'updates') {
            return ['bgp_updates', $peer->bgpPeer_id];
        }

        if (str_starts_with((string) $this->graph, 'prefixes_') && in_array(substr((string) $this->graph, 9), $afiSafis, true)) {
            return ["bgp_$this->graph", $peer->bgpPeer_id];
        }

        if ($macAccountingId && str_starts_with((string) $this->graph, 'macaccounting_')) {
            return [$this->graph, $macAccountingId];
        }

        return [null, $peer->bgpPeer_id];
    }
}
