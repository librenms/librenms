<?php

/**
 * BgpPeers.php
 *
 * BGP sessions and their per address family prefix counts.
 * The OS discovers the sessions, this module keeps them in sync and records their counters.
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
use App\Models\BgpPeer;
use App\Models\BgpPeerCbgp;
use App\Models\Device;
use App\Models\Eventlog;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Data\Store\Rrd;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\SnmpException;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\BgpAddressFamilyDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Traits\RequiresPollingMethod;
use LibreNMS\Util\AutonomousSystem;
use LibreNMS\Util\IP;
use LibreNMS\Util\Rewrite;

class BgpPeers implements Module
{
    use SyncsModels;
    use RequiresPollingMethod;

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
        return ['ports', 'vrf', 'ipv4-addresses', 'ipv6-addresses'];
    }

    public function pollingMethods(Device $device): array
    {
        return [PollingMethodType::Snmp];
    }

    public function discover(OS $os): void
    {
        $device = $os->getDevice();

        try {
            [$peers, $addressFamilies] = $this->fetch($os);
        } catch (SnmpException $e) {
            Log::warning('Device did not answer, BGP sessions were not updated: ' . $e->getMessage());

            return;
        }

        $device->bgpLocalAs = $os->discoverBgpLocalAs();
        $device->save();
        Log::info('Local AS: ' . ($device->bgpLocalAs ?? 'none'));

        $peers->each(function (BgpPeer $peer): void {
            $peer->fill(array_merge(self::PEER_DEFAULTS, $peer->getAttributes()));
            $peer->astext = AutonomousSystem::get($peer->bgpPeerRemoteAs)->name();
        });
        $addressFamilies->each(fn (BgpPeerCbgp $family) => $family->forceFill(array_merge(BgpPeerCbgp::emptyCounters(), $family->getAttributes())));

        ModuleModelObserver::observe(BgpPeer::class);
        $synced = $this->syncVrfContextModels($device, 'bgppeers', $peers);
        ModuleModelObserver::done();
        $device->unsetRelation('bgppeers');

        // a failed address family walk must not remove the families of sessions that still exist
        if ($addressFamilies->isNotEmpty() || $peers->isEmpty() || ! $os instanceof BgpAddressFamilyDiscovery) {
            ModuleModelObserver::observe(BgpPeerCbgp::class, 'Address families');
            $this->syncVrfContextModels($device, 'bgpPeersCbgp', $addressFamilies);
            ModuleModelObserver::done();
            $device->unsetRelation('bgpPeersCbgp');
        }

        if (LibrenmsConfig::get('autodiscovery.bgp')) {
            $synced->filter(fn (BgpPeer $peer) => $peer->wasRecentlyCreated && IP::isValid($peer->bgpPeerIdentifier))->each(function (BgpPeer $peer) use ($device): void {
                app(AutoDiscoverDevice::class)->execute(gethostbyaddr($peer->bgpPeerIdentifier) ?: $peer->bgpPeerIdentifier, $device, 'BGP');
            });
        }
    }

    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $device = $os->getDevice();
        $peers = $device->bgppeers()->whereIn('context_name', $device->getVrfContexts())->get();

        if ($peers->isEmpty()) {
            return;
        }

        try {
            [$polledPeers, $polledFamilies] = $this->fetch($os, $device->bgpPeersCbgp()->exists());
        } catch (SnmpException $e) {
            Log::warning('Device did not answer, BGP sessions were not polled: ' . $e->getMessage());

            return;
        }

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
                'rrd_oldname' => $this->uncompressedRrdName($peer->bgpPeerIdentifier, 'bgp-'),
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
                'rrd_oldname' => $this->uncompressedRrdName($addressFamily->bgpPeerIdentifier, 'cbgp-', ".$addressFamily->afi.$addressFamily->safi"),
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

    /**
     * Address families are only kept for the sessions found, the first one reported wins
     *
     * @return array{0: Collection<int, BgpPeer>, 1: Collection<int, BgpPeerCbgp>}
     *
     * @throws SnmpException
     */
    private function fetch(OS $os, bool $addressFamilies = true): array
    {
        $peers = $os->discoverBgpPeers();
        foreach ($peers->countBy('context_name') as $context => $count) {
            Log::info(($context ? "$context: " : '') . "$count peers");
        }

        if (! $addressFamilies || ! $os instanceof BgpAddressFamilyDiscovery) {
            return [$peers, new Collection];
        }

        $sessions = $peers->map(fn (BgpPeer $peer) => "$peer->bgpPeerIdentifier|$peer->context_name")->flip();
        $families = $os->discoverBgpAddressFamilies($peers)
            ->filter(fn (BgpPeerCbgp $family) => $sessions->has("$family->bgpPeerIdentifier|$family->context_name"))
            ->unique(fn (BgpPeerCbgp $family) => $family->getCompositeKey())
            ->values();

        return [$peers, $families];
    }

    /**
     * Some MIBs used to store IPv6 peers uncompressed, the datastore renames that rrd file before writing to the compressed one
     */
    private function uncompressedRrdName(string $identifier, string $prefix, string $suffix = ''): ?string
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
