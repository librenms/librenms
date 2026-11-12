<?php

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Models\Ipv6Address;
use App\Models\Ipv6Network;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Facades\Log;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Traits\RequiresPollingMethod;
use LibreNMS\Util\IPv6;

class Ipv6Addresses implements Module
{
    use SyncsModels;
    use RequiresPollingMethod;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return ['ports'];
    }

    public function pollingMethods(Device $device): array
    {
        return [PollingMethodType::Snmp];
    }

    /**
     * @inheritDoc
     */
    public function shouldPoll(OS $os): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $ips = $os->discoverIpv6Addresses();

        // reject localhost and populate ipv6 networks
        $ips = $ips->reject(function (Ipv6Address $ip) {
            if (! $ip->port_id) {
                Log::debug("Skipping $ip->ipv6_compressed due to no matching port");

                return true;
            }

            return $ip->ipv6_compressed === '::1';
        })->each(function (Ipv6Address $ip): void {
            $ipv6 = IPv6::parse($ip->ipv6_address);

            // do not set a network for link-local addresses as they exist on all interfaces
            if ($ipv6->isLinkLocal()) {
                $ip->ipv6_network_id = 0;

                return;
            }

            // if the os did not set a network id and the ip has a valid prefixlen (no host addresses)
            if ($ip->ipv6_network_id === null && $ip->ipv6_prefixlen > 0 && $ip->ipv6_prefixlen < 128) {
                $network = Ipv6Network::firstOrCreate([
                    'ipv6_network' => $ipv6->getNetwork($ip->ipv6_prefixlen),
                    'context_name' => $ip->context_name,
                ]);

                $ip->ipv6_network_id = $network->ipv6_network_id;
            }
        });

        ModuleModelObserver::observe(Ipv6Address::class);
        $this->syncVrfContextModels($os->getDevice(), 'ipv6', $ips);
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        // no polling
    }

    /**
     * @inheritDoc
     */
    public function dataExists(Device $device): bool
    {
        return $device->ipv6()->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->ipv6()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        if ($type == 'polling') {
            return null;
        }

        return [
            'ipv6_addresses' => $device->ipv6()
                ->leftJoin('ipv6_networks', 'ipv6_addresses.ipv6_network_id', 'ipv6_networks.ipv6_network_id')
                ->select(['ipv6_addresses.*', 'ipv6_network', 'ifIndex']) // already joined with ports
                ->orderBy('ipv6_address')->orderBy('ipv6_prefixlen')->orderBy('ifIndex')->orderBy('ipv6_addresses.context_name')
                ->get()->map->makeHidden(['ipv6_address_id', 'ipv6_network_id', 'port_id', 'laravel_through_key']),
        ];
    }
}
