<?php

/**
 * BgpPeers.php
 *
 * BGP sessions and their per address family prefix counts.
 * Vendor tables are described in os discovery yaml and read by BgpTableReader,
 * this module turns those rows into models and keeps them in sync.
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

namespace LibreNMS\Modules;

use App\Actions\Device\AutoDiscoverDevice;
use App\Facades\LibrenmsConfig;
use App\Facades\PortCache;
use App\Models\BgpPeer;
use App\Models\BgpPeerCbgp;
use App\Models\Device;
use App\Models\Eventlog;
use App\Models\VrfLite;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Data\Store\Rrd;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Discovery\Bgp\BgpTableReader;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ConnectivityHelper;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\AutonomousSystem;
use LibreNMS\Util\IP;
use LibreNMS\Util\Rewrite;

class BgpPeers implements Module
{
    use SyncsModels;

    /** BgpTableReader fields to bgpPeers columns */
    private const PEER_COLUMNS = [
        'remote_as' => 'bgpPeerRemoteAs',
        'state' => 'bgpPeerState',
        'admin_status' => 'bgpPeerAdminStatus',
        'local_address' => 'bgpLocalAddr',
        'descr' => 'bgpPeerDescr',
        'last_error_code' => 'bgpPeerLastErrorCode',
        'last_error_subcode' => 'bgpPeerLastErrorSubCode',
        'last_error_text' => 'bgpPeerLastErrorText',
        'in_updates' => 'bgpPeerInUpdates',
        'out_updates' => 'bgpPeerOutUpdates',
        'in_messages' => 'bgpPeerInTotalMessages',
        'out_messages' => 'bgpPeerOutTotalMessages',
        'established_time' => 'bgpPeerFsmEstablishedTime',
        'update_elapsed_time' => 'bgpPeerInUpdateElapsedTime',
    ];

    /** BgpTableReader fields to bgpPeers_cbgp columns */
    private const FAMILY_COLUMNS = [
        'accepted' => 'AcceptedPrefixes',
        'denied' => 'DeniedPrefixes',
        'admin_limit' => 'PrefixAdminLimit',
        'threshold' => 'PrefixThreshold',
        'clear_threshold' => 'PrefixClearThreshold',
        'advertised' => 'AdvertisedPrefixes',
        'suppressed' => 'SuppressedPrefixes',
        'withdrawn' => 'WithdrawnPrefixes',
    ];

    /** Values for columns that are required, but may not be reported by every table */
    private const PEER_DEFAULTS = [
        'bgpPeerState' => 'idle',
        'bgpPeerAdminStatus' => 'stop',
        'bgpPeerDescr' => '',
        'bgpLocalAddr' => '0.0.0.0',
        'bgpPeerInUpdates' => 0,
        'bgpPeerOutUpdates' => 0,
        'bgpPeerInTotalMessages' => 0,
        'bgpPeerOutTotalMessages' => 0,
        'bgpPeerFsmEstablishedTime' => 0,
        'bgpPeerInUpdateElapsedTime' => 0,
    ];

    /**
     * @return string[]
     */
    public function dependencies(): array
    {
        return ['ports', 'vrf', 'cisco-vrf-lite', 'ipv4-addresses', 'ipv6-addresses'];
    }

    public function shouldDiscover(OS $os, ModuleStatus $status, ConnectivityHelper $connectivity): bool
    {
        return $status->isEnabled() && $connectivity->snmpIsAvailable();
    }

    public function shouldPoll(OS $os, ModuleStatus $status, ConnectivityHelper $connectivity): bool
    {
        return $status->isEnabled() && $connectivity->snmpIsAvailable();
    }

    public function discover(OS $os): void
    {
        $device = $os->getDevice();
        $reader = $this->reader($os);

        $contexts = $reader->reportsAllVrfs() ? [''] : $this->contexts($device);
        [$peers, $addressFamilies, $rows] = $this->fetch($reader, $contexts, $device);

        $device->bgpLocalAs = $reader->localAs($rows);
        $device->save();
        Log::info('Local AS: ' . ($device->bgpLocalAs ?? 'none'));

        $peers->each(function (BgpPeer $peer): void {
            $peer->fill(array_merge(self::PEER_DEFAULTS, $peer->getAttributes()));
            $peer->astext = AutonomousSystem::get($peer->bgpPeerRemoteAs)->name();
        });
        $addressFamilies->each(fn (BgpPeerCbgp $family) => $family->forceFill(array_merge(BgpPeerCbgp::emptyCounters(), $family->getAttributes())));

        ModuleModelObserver::observe(BgpPeer::class);
        $synced = $this->syncModels($device, 'bgppeers', $peers);
        ModuleModelObserver::done();

        ModuleModelObserver::observe(BgpPeerCbgp::class, 'Address families');
        $this->syncModels($device, 'bgpPeersCbgp', $addressFamilies);
        ModuleModelObserver::done();

        if (LibrenmsConfig::get('autodiscovery.bgp')) {
            $synced->filter(fn (BgpPeer $peer) => $peer->wasRecentlyCreated && IP::isValid($peer->bgpPeerIdentifier))->each(function (BgpPeer $peer) use ($device): void {
                app(AutoDiscoverDevice::class)->execute(gethostbyaddr($peer->bgpPeerIdentifier) ?: $peer->bgpPeerIdentifier, $device, 'BGP');
            });
        }
    }

    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $device = $os->getDevice();
        $peers = $device->bgppeers()->get();

        if ($peers->isEmpty()) {
            return;
        }

        $reader = $this->reader($os);
        $contexts = $reader->reportsAllVrfs() ? [''] : $peers->pluck('context_name')->unique()->all();
        [$polledPeers, $polledFamilies] = $this->fetch($reader, $contexts, $device);
        $polledPeers = $polledPeers->keyBy->getCompositeKey();
        $polledFamilies = $polledFamilies->keyBy->getCompositeKey();

        foreach ($peers as $peer) {
            $polled = $polledPeers->get($peer->getCompositeKey());
            if ($polled === null) {
                Log::debug("BGP peer $peer->bgpPeerIdentifier ($peer->context_name) not found");
                continue;
            }

            $peer->fill($polled->getAttributes());
            $this->logStateChange($peer, $device);
            $peer->save();

            $datastore->put($os->getDeviceArray(), 'bgp', [
                'bgpPeerIdentifier' => $peer->bgpPeerIdentifier,
                'rrd_name' => Rrd::safeName('bgp-' . $peer->bgpPeerIdentifier),
                'rrd_oldname' => $this->uncompressedRrdName('bgp-', $peer->bgpPeerIdentifier),
                'rrd_def' => RrdDefinition::make()
                    ->addDataset('bgpPeerOutUpdates', 'COUNTER', null, 100000000000)
                    ->addDataset('bgpPeerInUpdates', 'COUNTER', null, 100000000000)
                    ->addDataset('bgpPeerOutTotal', 'COUNTER', null, 100000000000)
                    ->addDataset('bgpPeerInTotal', 'COUNTER', null, 100000000000)
                    ->addDataset('bgpPeerEstablished', 'GAUGE', 0),
            ], [
                'bgpPeerOutUpdates' => $peer->bgpPeerOutUpdates,
                'bgpPeerInUpdates' => $peer->bgpPeerInUpdates,
                'bgpPeerOutTotal' => $peer->bgpPeerOutTotalMessages,
                'bgpPeerInTotal' => $peer->bgpPeerInTotalMessages,
                'bgpPeerEstablished' => $peer->bgpPeerFsmEstablishedTime,
            ]);
        }

        foreach ($device->bgpPeersCbgp()->get() as $addressFamily) {
            $polled = $polledFamilies->get($addressFamily->getCompositeKey());
            if ($polled === null) {
                continue;
            }

            foreach (BgpPeerCbgp::PREFIX_COUNTERS as $counter) {
                $value = (int) $polled->$counter;
                if (in_array($counter, BgpPeerCbgp::DELTA_COUNTERS)) {
                    $addressFamily->{$counter . '_prev'} = (int) $addressFamily->$counter;
                    $addressFamily->{$counter . '_delta'} = $value - (int) $addressFamily->$counter;
                }
                $addressFamily->$counter = $value;
            }
            $addressFamily->save();

            $datastore->put($os->getDeviceArray(), 'cbgp', [
                'bgpPeerIdentifier' => $addressFamily->bgpPeerIdentifier,
                'afi' => $addressFamily->afi,
                'safi' => $addressFamily->safi,
                'rrd_name' => Rrd::safeName("cbgp-$addressFamily->bgpPeerIdentifier.$addressFamily->afi.$addressFamily->safi"),
                'rrd_oldname' => $this->uncompressedRrdName('cbgp-', $addressFamily->bgpPeerIdentifier, ".$addressFamily->afi.$addressFamily->safi"),
                'rrd_def' => RrdDefinition::make()
                    ->addDataset('AcceptedPrefixes', 'GAUGE', null, 100000000000)
                    ->addDataset('DeniedPrefixes', 'GAUGE', null, 100000000000)
                    ->addDataset('AdvertisedPrefixes', 'GAUGE', null, 100000000000)
                    ->addDataset('SuppressedPrefixes', 'GAUGE', null, 100000000000)
                    ->addDataset('WithdrawnPrefixes', 'GAUGE', null, 100000000000),
            ], $addressFamily->only(BgpPeerCbgp::DELTA_COUNTERS));
        }
    }

    public function dataExists(Device $device): bool
    {
        return $device->bgppeers()->exists() || $device->bgpPeersCbgp()->exists();
    }

    public function cleanup(Device $device): int
    {
        return $device->bgppeers()->delete() + $device->bgpPeersCbgp()->delete();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function dump(Device $device, string $type): ?array
    {
        return [
            'bgpPeers' => $device->bgppeers()
                ->leftJoin('devices', 'bgpPeers.device_id', '=', 'devices.device_id')
                ->leftJoin('vrfs', 'bgpPeers.vrf_id', '=', 'vrfs.vrf_id')
                ->select(['bgpPeers.*', 'devices.bgpLocalAs', 'vrfs.vrf_name'])
                ->orderBy('bgpPeerIdentifier')->orderBy('context_name')->orderBy('vrf_name')
                ->get()->map->makeHidden(['bgpPeer_id', 'device_id', 'vrf_id']),
            'bgpPeers_cbgp' => $device->bgpPeersCbgp()
                ->orderBy('bgpPeerIdentifier')->orderBy('afi')->orderBy('safi')
                ->get()->map->makeHidden(['device_id']),
        ];
    }

    private function reader(OS $os): BgpTableReader
    {
        return app(BgpTableReader::class, ['definition' => $os->getDiscovery('bgp-peers')]);
    }

    /**
     * @param  string[]  $contexts
     * @return array{0: Collection<int, BgpPeer>, 1: Collection<int, BgpPeerCbgp>, 2: array<int, array<string, mixed>>}
     */
    private function fetch(BgpTableReader $reader, array $contexts, Device $device): array
    {
        $peers = new Collection;
        $addressFamilies = new Collection;
        $rows = [];

        foreach ($contexts as $context) {
            $data = $reader->read($context);
            $found = collect($data['peers'])
                ->map(fn (array $row) => $this->makePeer($row, $context, $device))
                ->reject(fn (BgpPeer $peer) => in_array($peer->bgpPeerIdentifier, ['0.0.0.0', '::']));
            $identifiers = $found->pluck('bgpPeerIdentifier')->flip();

            $peers = $peers->merge($found);
            $addressFamilies = $addressFamilies->merge(collect($data['address_families'])
                ->map(fn (array $row) => $this->makeAddressFamily($row, $context, $device))
                ->filter(fn (BgpPeerCbgp $family) => $identifiers->has($family->bgpPeerIdentifier)));
            $rows = [...$rows, ...$data['peers']];

            Log::info(($context ? "$context: " : '') . $found->count() . ' peers');
        }

        return [$peers, $addressFamilies, $rows];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function makePeer(array $row, string $context, Device $device): BgpPeer
    {
        $peer = new BgpPeer([
            'bgpPeerIdentifier' => $this->identifier($row, $device),
            'bgpPeerRemoteAddr' => isset($row['address']) ? $row['address']->uncompressed() : '',
            'context_name' => $context,
        ]);

        foreach (self::PEER_COLUMNS as $field => $column) {
            if (isset($row[$field])) {
                $peer->$column = $row[$field];
            }
        }

        $peer->vrf_id = $this->vrfId($row, $device);
        $peer->bgpPeerIface = $this->ifIndex($row, $peer, $device);

        return $peer;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function makeAddressFamily(array $row, string $context, Device $device): BgpPeerCbgp
    {
        $family = new BgpPeerCbgp([
            'bgpPeerIdentifier' => $this->identifier($row, $device),
            'afi' => $row['afi'],
            'safi' => $row['safi'],
            'context_name' => $context,
        ]);

        foreach (self::FAMILY_COLUMNS as $field => $column) {
            if (isset($row[$field])) {
                $family->$column = $row[$field];
            }
        }

        // tables that report withdrawn prefixes (CISCO-BGP4-MIB) count both as running totals
        if (isset($family->WithdrawnPrefixes)) {
            $family->AdvertisedPrefixes = max(0, (int) $family->AdvertisedPrefixes - $family->WithdrawnPrefixes);
            $family->WithdrawnPrefixes = 0;
        }

        return $family;
    }

    /**
     * The default context is always read, VRF-lite contexts mapped to the default VRF would only repeat it
     *
     * @return string[]
     */
    private function contexts(Device $device): array
    {
        $vrfLiteContexts = $device->vrfLites
            ->reject(fn (VrfLite $vrfLite) => strcasecmp((string) $vrfLite->vrf_name, 'default') == 0)
            ->pluck('context_name')->all();

        return array_values(array_unique([
            '',
            ...$vrfLiteContexts,
            ...json_decode((string) $device->getAttrib('routing_snmp_contexts', '[]'), true) ?: [],
        ]));
    }

    /**
     * Peers are identified by address, or by interface name when the address is only unique on its link
     *
     * @param  array<string, mixed>  $row
     */
    private function identifier(array $row, Device $device): string
    {
        if (! BgpTableReader::identifiedByInterface($row)) {
            return $row['address']->compressed();
        }

        $iface = (string) $row['iface'];

        return is_numeric($iface) ? PortCache::getNameFromIfIndex((int) $iface, $device) ?? $iface : $iface;
    }

    /**
     * Tables identify the VRF by name or by the vrf_oid discovered by the vrf module
     *
     * @param  array<string, mixed>  $row
     */
    private function vrfId(array $row, Device $device): ?int
    {
        if (isset($row['vrf_name'])) {
            return $device->vrfs->firstWhere('vrf_name', $row['vrf_name'])?->vrf_id;
        }

        if (isset($row['vrf'])) {
            return $device->vrfs->firstWhere('vrf_oid', $row['vrf'])?->vrf_id;
        }

        return null;
    }

    /**
     * Tables may report the interface as ifIndex or ifName, otherwise find it by the session's local address
     *
     * @param  array<string, mixed>  $row
     */
    private function ifIndex(array $row, BgpPeer $peer, Device $device): ?int
    {
        $iface = $row['iface'] ?? null;
        if (is_numeric($iface)) {
            return (int) $iface;
        }

        $ifIndex = $iface ? PortCache::getByIfName($iface, $device)?->ifIndex : null;
        if ($ifIndex !== null) {
            return $ifIndex;
        }

        $ip = IP::parse((string) $peer->bgpLocalAddr, true);
        if ($ip === null || in_array($ip->compressed(), ['0.0.0.0', '::'])) {
            return null;
        }

        $portId = PortCache::getIdFromIp($ip, $peer->context_name, $device);

        return $portId ? PortCache::get($portId)?->ifIndex : null;
    }

    /**
     * Some MIBs used to store IPv6 peers uncompressed, rename their rrd files
     */
    private function uncompressedRrdName(string $prefix, string $identifier, string $suffix = ''): ?string
    {
        $ip = IP::parse($identifier, true);

        return $ip?->getFamily() == 'ipv6' ? Rrd::safeName($prefix . $ip->uncompressed() . $suffix) : null;
    }

    private function logStateChange(BgpPeer $peer, Device $device): void
    {
        if (! $peer->bgpPeerFsmEstablishedTime || ! ($peer->isDirty('bgpPeerState') || $peer->bgpPeerFsmEstablishedTime < $peer->getOriginal('bgpPeerFsmEstablishedTime'))) {
            return;
        }

        $whitelist = LibrenmsConfig::get('alerts.bgp.whitelist');
        if (is_array($whitelist) && ! in_array($peer->bgpPeerRemoteAs, $whitelist)) {
            return;
        }

        $description = "$peer->bgpPeerIdentifier (AS$peer->bgpPeerRemoteAs $peer->bgpPeerDescr)";
        $error = Rewrite::bgpErrorCode($peer->bgpPeerLastErrorCode ?? '', $peer->bgpPeerLastErrorSubCode ?? '');

        if (! $peer->isDirty('bgpPeerState')) {
            Eventlog::log("BGP Session Flap: $description, last error: $error", $device, 'bgpPeer', Severity::Warning, $peer->bgpPeerIdentifier);
        } elseif ($peer->bgpPeerState == 'established') {
            Eventlog::log("BGP Session Up: $description", $device, 'bgpPeer', Severity::Ok, $peer->bgpPeerIdentifier);
        } elseif ($peer->getOriginal('bgpPeerState') == 'established') {
            Eventlog::log("BGP Session Down: $description, last error: $error", $device, 'bgpPeer', Severity::Error, $peer->bgpPeerIdentifier);
        }
    }
}
