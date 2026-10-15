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
use App\Models\Vrf;
use App\Models\VrfLite;
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
 *     prefixes: list<array{afisafi: string, accepted: int, limit: int, percent: int|null, class: string}>,
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
        $peers->loadMissing('device');
        $peers = self::filterForGraph($peers, $graph);

        $cbgp = $this->loadCbgp($peers);
        $linkedPorts = $this->resolveLinkedPorts($peers);
        $localPorts = $this->resolveLocalPorts($peers);
        $macAccountingIds = str_starts_with((string) $graph, 'macaccounting_') ? self::macAccountingIds($peers) : [];

        // only show the vrf column when at least one peer is in a vrf (snmp context or vrf_id)
        $this->showVrf = $peers->contains(fn (BgpPeer $p) => ! empty($p->context_name) || ! empty($p->vrf_id));
        $vrfNames = $this->showVrf ? $this->resolveVrfNames($peers) : [];
        $this->showPrefixes = $cbgp->isNotEmpty();

        $this->rows = $peers->map(fn (BgpPeer $peer) => $this->formatPeer(
            $peer,
            $this->peerCbgp($peer, $cbgp),
            $linkedPorts[$peer->device_id . '|' . $peer->bgpPeerIdentifier] ?? null,
            $localPorts[$peer->device_id . '|' . $peer->bgpPeerIface] ?? null,
            $this->vrfName($peer, $vrfNames),
            $macAccountingIds[$peer->device_id . '|' . $peer->bgpPeerIdentifier] ?? null,
        ))->values()->all();
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
    private function formatPeer(BgpPeer $peer, Collection $cbgp, ?Port $linkedPort, ?Port $localPort, string $vrf, ?int $macAccountingId): array
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
            'vrf' => $vrf,
            'peer_type' => $peerType,
            'peer_type_class' => $peerTypeClass,
            'afi_list' => $cbgp->map(fn (BgpPeerCbgp $c) => "$c->afi.$c->safi")->implode(', '),
            'admin_color' => in_array($peer->bgpPeerAdminStatus, ['start', 'running'], true) ? 'success' : 'default',
            'state_color' => $peer->bgpPeerState === 'established' ? 'success' : 'danger',
            'last_error' => $this->formatLastError($peer),
            'uptime' => Time::formatInterval($peer->bgpPeerFsmEstablishedTime),
            'in_updates' => Number::formatSi($peer->bgpPeerInUpdates, 2, 0, ''),
            'out_updates' => Number::formatSi($peer->bgpPeerOutUpdates, 2, 0, ''),
            'prefixes' => $cbgp->map(fn (BgpPeerCbgp $c) => $this->formatPrefixLimit($c))->values()->all(),
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
            ->groupBy(fn (BgpPeerCbgp $c) => "$c->device_id|$c->bgpPeerIdentifier")
            ->toBase();
    }

    /**
     * The address families of a peer. Peers in an snmp context (cisco vrf-lite) only use the rows of their context,
     * the same address can exist in other contexts. Os specific modules (vrf_id based) store their peers without
     * context but their cbgp rows with the vrf name, so those match on address only.
     *
     * @param  Collection<array-key, EloquentCollection<int, BgpPeerCbgp>>  $cbgp
     * @return Collection<int, BgpPeerCbgp>
     */
    private function peerCbgp(BgpPeer $peer, Collection $cbgp): Collection
    {
        $peerCbgp = $cbgp->get("$peer->device_id|$peer->bgpPeerIdentifier", collect());

        return empty($peer->context_name) ? $peerCbgp : $peerCbgp->where('context_name', $peer->context_name);
    }

    /**
     * Vrf names by vrf_id (vrf_id based os modules) and by device_id|context_name (cisco vrf-lite)
     *
     * @param  Collection<int, BgpPeer>  $peers
     * @return array{ids: array<int, string>, contexts: array<string, string>}
     */
    private function resolveVrfNames(Collection $peers): array
    {
        $vrfIds = $peers->pluck('vrf_id')->filter()->unique();

        return [
            'ids' => $vrfIds->isEmpty() ? [] : Vrf::whereIn('vrf_id', $vrfIds)->pluck('vrf_name', 'vrf_id')->all(),
            'contexts' => VrfLite::whereIn('device_id', $peers->pluck('device_id')->unique())->get()
                ->mapWithKeys(fn (VrfLite $vrf) => ["$vrf->device_id|$vrf->context_name" => (string) $vrf->vrf_name])
                ->all(),
        ];
    }

    /**
     * @param  array{ids?: array<int, string>, contexts?: array<string, string>}  $vrfNames
     */
    private function vrfName(BgpPeer $peer, array $vrfNames): string
    {
        if ($peer->vrf_id && isset($vrfNames['ids'][$peer->vrf_id])) {
            return $vrfNames['ids'][$peer->vrf_id];
        }

        return $vrfNames['contexts']["$peer->device_id|$peer->context_name"] ?? (string) $peer->context_name;
    }

    /**
     * The local ports the sessions are terminated on, keyed by device_id|ifIndex
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

        return $ip && ! in_array((string) $ip, ['0.0.0.0', '::'], true) ? $ip->compressed() : '';
    }

    /**
     * @return array{afisafi: string, accepted: int, limit: int, percent: int|null, class: string}
     */
    private function formatPrefixLimit(BgpPeerCbgp $cbgp): array
    {
        $accepted = (int) $cbgp->AcceptedPrefixes;
        $limit = (int) $cbgp->PrefixAdminLimit;
        $percent = $limit > 0 ? (int) round($accepted / $limit * 100) : null;

        $class = match (true) {
            $percent === null => '',
            $percent >= 100 => 'text-danger',
            $cbgp->PrefixThreshold > 0 && $percent >= $cbgp->PrefixThreshold => 'text-warning',
            default => '',
        };

        return [
            'afisafi' => "$cbgp->afi.$cbgp->safi",
            'accepted' => $accepted,
            'limit' => $limit,
            'percent' => $percent,
            'class' => $class,
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
        $ipv4s = [];
        $ipv6s = [];
        foreach ($peers as $peer) {
            if (! str_contains($peer->bgpPeerIdentifier, ':')) {
                $ipv4s[$peer->bgpPeerIdentifier][] = $peer;
            } elseif ($parsed = IP::parse($peer->bgpPeerIdentifier, true)) {
                $ipv6s[$parsed->uncompressed()][] = $peer;
            }
        }

        $ports = [];

        if (! empty($ipv4s)) {
            foreach (Ipv4Address::whereIn('ipv4_address', array_keys($ipv4s))->with('port.device')->get() as $ip) {
                foreach ($ip->port ? $ipv4s[$ip->ipv4_address] : [] as $peer) {
                    $ports["$peer->device_id|$peer->bgpPeerIdentifier"] = $ip->port;
                }
            }
        }

        if (! empty($ipv6s)) {
            foreach (Ipv6Address::whereIn('ipv6_address', array_keys($ipv6s))->with('port.device')->get() as $ip) {
                foreach ($ip->port ? ($ipv6s[$ip->ipv6_address] ?? []) : [] as $peer) {
                    $ports["$peer->device_id|$peer->bgpPeerIdentifier"] = $ip->port;
                }
            }
        }

        return $ports;
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
