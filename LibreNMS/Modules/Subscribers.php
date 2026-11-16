<?php

/**
 * Subscribers.php
 *
 * Subscriber sessions of BNG/BRAS devices, for the device and per access port.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Models\RadiusServer;
use App\Models\SubscriberCount;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\RadiusServerDiscovery;
use LibreNMS\Interfaces\Discovery\SubscriberDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Traits\RequiresPollingMethod;

class Subscribers implements Module
{
    use SyncsModels;
    use RequiresPollingMethod;

    /**
     * @return string[]
     */
    public function dependencies(): array
    {
        return ['ports'];
    }

    public function pollingMethods(Device $device): array
    {
        return [PollingMethodType::Snmp];
    }

    public function discover(OS $os): void
    {
        if (! $os instanceof SubscriberDiscovery) {
            if ($this->dataExists($os->getDevice())) {
                $this->cleanup($os->getDevice()); // the os no longer reports subscribers
            }

            return;
        }

        $counts = $os->discoverSubscribers();
        if ($counts === null) {
            Log::warning('Device did not answer, subscribers were not updated');

            return;
        }

        $counts = self::normalize($counts);

        // devices may report 0 without being a BNG (no PPP server, a wireless controller),
        // they are a subscriber device once there were sessions and then kept, so a drop to 0 is seen
        if ($counts->sum('sessions') === 0 && ! $os->getDevice()->subscriberCounts()->exists()) {
            $counts = new Collection;
        }

        ModuleModelObserver::observe(SubscriberCount::class);
        $this->syncModels($os->getDevice(), 'subscriberCounts', $counts, $os->getDevice()->subscriberCounts()->get());
        ModuleModelObserver::done();

        $this->discoverRadiusServers($os);
    }

    /**
     * Only for devices with subscribers, other devices may use RADIUS for logins or 802.1X
     */
    private function discoverRadiusServers(OS $os): void
    {
        $device = $os->getDevice();
        if (! $os instanceof RadiusServerDiscovery) {
            return;
        }

        if (! $device->subscriberCounts()->exists()) {
            $device->radiusServers()->delete();

            return;
        }

        $servers = $os->discoverRadiusServers();
        if ($servers === null) {
            Log::warning('Device did not answer, RADIUS servers were not updated');

            return;
        }

        ModuleModelObserver::observe(RadiusServer::class, 'RADIUS servers');
        $this->syncModels($device, 'radiusServers', $servers, $device->radiusServers()->get());
        ModuleModelObserver::done();
    }

    /**
     * Counts the device no longer reports are set to 0 until discovery removes them,
     * so all sessions of a port dropping is seen and can be alerted on. New counts are added.
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        if (! $os instanceof SubscriberDiscovery) {
            return;
        }

        $existing = $os->getDevice()->subscriberCounts()->get();
        if ($existing->isEmpty()) {
            return;
        }

        $polled = $os->discoverSubscribers();
        if ($polled === null) {
            Log::warning('Device did not answer, subscribers were not polled');

            return;
        }

        $polled = self::normalize($polled)->keyBy->getCompositeKey();
        $existing = $existing->toBase()->keyBy->getCompositeKey(); // an eloquent merge would key by id
        $os->getDevice()->subscriberCounts()->saveMany($polled->diffKeys($existing));

        foreach ($existing->merge($polled->diffKeys($existing)) as $key => $count) {
            $count->sessions = $polled->get($key)->sessions ?? 0;
            $count->save();

            if ($count->port_id === null) {
                $datastore->put($os->getDeviceArray(), 'subscribers', [
                    'session_type' => $count->session_type,
                    'rrd_name' => ['subscribers', $count->session_type],
                    'rrd_def' => RrdDefinition::make()->addDataset('sessions', 'GAUGE', 0),
                ], ['sessions' => $count->sessions]);
            }
        }

        $this->pollRadiusServers($os, $datastore);
    }

    private function pollRadiusServers(OS $os, DataStorageInterface $datastore): void
    {
        $existing = $os->getDevice()->radiusServers()->get();
        if ($existing->isEmpty() || ! $os instanceof RadiusServerDiscovery) {
            return;
        }

        $polled = $os->discoverRadiusServers();
        if ($polled === null) {
            Log::warning('Device did not answer, RADIUS servers were not polled');

            return;
        }

        $polled = $polled->keyBy->getCompositeKey();
        foreach ($existing as $server) {
            $current = $polled->get($server->getCompositeKey());
            if ($current === null) {
                continue; // removed at discovery
            }

            $server->fill($current->only(['state', 'dead_count', 'requests', 'accepts', 'rejects', 'timeouts', 'round_trip_time']));
            $server->save();

            $datastore->put($os->getDeviceArray(), 'radius', [
                'address' => $server->address,
                'port' => $server->port,
                'rrd_name' => ['radius', "$server->address-$server->port"],
                'rrd_def' => RrdDefinition::make()
                    ->addDataset('requests', 'COUNTER', 0)
                    ->addDataset('accepts', 'COUNTER', 0)
                    ->addDataset('rejects', 'COUNTER', 0)
                    ->addDataset('timeouts', 'COUNTER', 0)
                    ->addDataset('rtt', 'GAUGE', 0),
            ], [
                'requests' => $server->requests,
                'accepts' => $server->accepts,
                'rejects' => $server->rejects,
                'timeouts' => $server->timeouts,
                'rtt' => $server->round_trip_time,
            ]);
        }
    }

    /**
     * Rules every os gets: unknown session types are 'other', counts with the same port and type are added,
     * and a port total is only kept when the port has no session types (it is their sum).
     *
     * @param  Collection<int, SubscriberCount>  $counts
     * @return Collection<int, SubscriberCount>
     */
    public static function normalize(Collection $counts): Collection
    {
        $counts = $counts
            ->each(function (SubscriberCount $count): void {
                $count->session_type = array_key_exists((string) $count->session_type, SubscriberCount::SESSION_TYPES) ? $count->session_type : 'other';
            })
            ->groupBy(fn (SubscriberCount $count) => $count->getCompositeKey())
            ->map(fn (Collection $same) => $same->first()->fill(['sessions' => $same->sum('sessions')]));

        $typedPorts = $counts->filter(fn (SubscriberCount $count) => $count->port_id !== null && $count->session_type !== 'total')->pluck('port_id')->flip();

        return $counts->reject(fn (SubscriberCount $count) => $count->session_type === 'total' && $typedPorts->has($count->port_id))->values();
    }

    public function dataExists(Device $device): bool
    {
        return $device->subscriberCounts()->exists() || $device->radiusServers()->exists();
    }

    public function cleanup(Device $device): int
    {
        return $device->subscriberCounts()->delete() + $device->radiusServers()->delete();
    }

    /**
     * @return array{subscriber_counts: array<int, array<string, mixed>>, radius_servers: array<int, array<string, mixed>>}
     */
    public function dump(Device $device, string $type): array
    {
        return [
            'subscriber_counts' => $device->subscriberCounts()
                ->leftJoin('ports', 'subscriber_counts.port_id', '=', 'ports.port_id')
                ->orderBy('ports.ifIndex')->orderBy('session_type')
                ->get(['ports.ifIndex', 'subscriber_counts.session_type', 'subscriber_counts.sessions'])
                ->toArray(),
            'radius_servers' => $device->radiusServers()->orderBy('address')->orderBy('port')
                ->get()->makeHidden(['id', 'device_id'])->toArray(),
        ];
    }
}
