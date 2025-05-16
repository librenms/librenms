<?php

namespace LibreNMS\Modules;

use App\Facades\PortCache;
use App\Models\Device;
use App\Models\Ipv4Mac;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\ArpTableDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\Util\Mac;
use SnmpQuery;

class ArpTable implements Module
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
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $arp = $os instanceof ArpTableDiscovery
            ? $os->discoverArpTable()
            : $this->discoverArpFromIpMib($os->getDevice());

        ModuleModelObserver::observe(Ipv4Mac::class);
        $this->syncModels($os->getDevice(), 'macs', $arp);
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
        return $device->macs()->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->macs()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        return [
            'ipv4_mac' => $device->macs()
                ->orderBy('context_name')->orderBy('ipv4_address')->orderBy('mac_address')
                ->get()->map->makeHidden(['id', 'device_id', 'port_id']),
        ];
    }

    private function discoverArpFromIpMib(Device $device): Collection
    {
        $arp = new Collection;

        foreach ($device->getVrfContexts() as $context_name) {
            $arp_data = SnmpQuery::context($context_name)->cache()->walk('IP-MIB::ipNetToPhysicalPhysAddress')->table(1);
            SnmpQuery::context($context_name)->walk('IP-MIB::ipNetToMediaPhysAddress')->table(1, $arp_data);

            foreach ($arp_data as $ifIndex => $data) {
                $port_id = PortCache::getIdFromIfIndex($ifIndex, $device);

                if (! $port_id) {
                    Log::debug("Skipping arp on interface with index $ifIndex - interface not found (hint: was it filtered out with bad_if/bad_if_regexp/bad_iftype/bad_ifoperstatus?)");
                    continue;
                }

                $port_arp = array_merge(
                    Arr::wrap($data['IP-MIB::ipNetToMediaPhysAddress'] ?? []),
                    Arr::wrap($data['IP-MIB::ipNetToPhysicalPhysAddress']['ipv4'] ?? []),
                );

                foreach ($port_arp as $ip => $raw_mac) {
                    // avoid invalid output IP-MIB::ipNetToPhysicalPhysAddress[17][ipv4][.10.19.0.9 = 18:94:ef:13:eb:88
                    $ip = Str::chopStart($ip, '.');

                    if (empty($ip) || empty($raw_mac) || $raw_mac == '0:0:0:0:0:0') {
                        continue;
                    }

                    $arp->push(new Ipv4Mac([
                        'port_id' => $port_id,
                        'mac_address' => Mac::parse($raw_mac)->hex(),
                        'ipv4_address' => $ip,
                        'context_name' => $context_name,
                    ]));
                }
            }
        }

        return $arp;
    }
}
