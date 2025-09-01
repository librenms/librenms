<?php

namespace LibreNMS\Modules;

use App\Facades\PortCache;
use App\Models\Device;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\Ipv6NdDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\Util\IPv6;
use LibreNMS\Util\Mac;
use SnmpQuery;

class Ipv6Nd implements Module
{
    use SyncsModels;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return ['ports'];
    }

    /**
     * @inheritDoc
     */
    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * @inheritDoc
     */
    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $arp = $os instanceof Ipv6NdDiscovery
            ? $os->discoverIpv6Neighbor()
            : $this->discoverNeighborsFromIpMib($os->getDevice());

        ModuleModelObserver::observe(\App\Models\Ipv6Nd::class);
        $this->syncModels($os->getDevice(), 'nd', $arp);
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $this->discover($os);
    }

    /**
     * @inheritDoc
     */
    public function dataExists(Device $device): bool
    {
        return $device->nd()->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->nd()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        return [
            'ipv6_nd' => $device->nd()
                ->orderBy('context_name')->orderBy('ipv6_address')->orderBy('mac_address')
                ->get()->map->makeHidden(['id', 'created_at', 'updated_at', 'device_id', 'port_id']),
        ];
    }

    private function discoverNeighborsFromIpMib(Device $device): Collection
    {
        $neighbors = new Collection;

        foreach ($device->getVrfContexts() as $context_name) {
            $arp_data = SnmpQuery::context($context_name)->cache()->walk('IP-MIB::ipNetToPhysicalPhysAddress')->table(1);

            foreach ($arp_data as $ifIndex => $data) {
                $port_id = PortCache::getIdFromIfIndex($ifIndex, $device);

                if (! $port_id) {
                    Log::debug("Skipping arp on interface with index $ifIndex - interface not found (hint: was it filtered out with bad_if/bad_if_regexp/bad_iftype/bad_ifoperstatus?)");
                    continue;
                }

                foreach ($data['IP-MIB::ipNetToPhysicalPhysAddress']['ipv6'] ?? [] as $ipv6 => $raw_mac) {
                    try {
                        $neighbors->push(new \App\Models\Ipv6Nd([
                            'port_id' => $port_id,
                            'device_id' => $device->device_id,
                            'mac_address' => Mac::parse($raw_mac)->readable(),
                            'ipv6_address' => IPv6::fromHexString($ipv6)->uncompressed(),
                            'context_name' => $context_name,
                        ]));
                    } catch (InvalidIpException $e) {
                        Log::error($e->getMessage());
                    }
                }
            }
        }

        return $neighbors;
    }
}
