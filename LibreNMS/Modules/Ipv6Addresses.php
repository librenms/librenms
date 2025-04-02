<?php

namespace LibreNMS\Modules;

use App\Facades\PortCache;
use App\Models\Device;
use App\Models\Ipv6Address;
use App\Models\Ipv6Network;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\Ipv6AddressDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\Util\IP;
use LibreNMS\Util\IPv4;
use LibreNMS\Util\IPv6;
use SnmpQuery;

class Ipv6Addresses implements Module
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
        $ips = new Collection;
        if ($os instanceof Ipv6AddressDiscovery) {
            $ips = $os->discoverIpv6Addresses();
        }
        if ($ips->isEmpty()) {
            $ips = $this->discoverIpMib($os->getDevice());
        }
        if ($ips->isEmpty()) {
            $ips = $this->discoverIpv6Mib($os->getDevice());
        }

        // reject localhost and populate ipv6 networks
        $ips = $ips->reject(function (Ipv6Address $ip) {
            if (! $ip->port_id) {
                Log::debug("Skipping $ip->ipv6_compressed due to no matching port");

                return true;
            }

            return $ip->ipv6_compressed === '::1';
        })->each(function (Ipv6Address $ip) {
            $ipv6 = IPv6::parse($ip->ipv6_address);

            // do not set an network for link-local addresses as the exist on all interfaces
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
        $this->syncModels($os->getDevice(), 'ipv6', $ips);
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

    private function discoverIpMib(Device $device): Collection
    {
        $ips = new Collection;
        foreach ($device->getVrfContexts() as $context_name) {
            $ips = $ips->merge(SnmpQuery::context($context_name)
                ->enumStrings()
                ->walk([
                    'IP-MIB::ipAddressTable',
                ])->mapTable(function ($data, $ipAddressAddrType, $ipAddressAddr = '') use ($context_name, $device) {
                    if (! in_array($ipAddressAddrType, ['ipv6', 'ipv6z'])) {
                        return null;
                    }

                    try {
                        Log::debug("Attempting to parse $ipAddressAddr");
                        $ifIndex = $data['IP-MIB::ipAddressIfIndex'] ?? 0;
                        $ip = $this->parseIp($ipAddressAddr, $ifIndex);

                        return new Ipv6Address([
                            'port_id' => PortCache::getIdFromIfIndex($ifIndex, $device),
                            'ipv6_address' => $ip->uncompressed(),
                            'ipv6_compressed' => $ip->compressed(),
                            'ipv6_prefixlen' => $this->parsePrefix($data['IP-MIB::ipAddressPrefix'] ?? ''),
                            'ipv6_origin' => $data['IP-MIB::ipAddressOrigin'] ?? 'unknown',
                            'context_name' => $context_name,
                        ]);
                    } catch (InvalidIpException $e) {
                        Log::error('Failed to parse IP: ' . $e->getMessage());

                        return null;
                    }
                }));
        }

        return $ips->filter();
    }

    public function discoverIpv6Mib(Device $device): Collection
    {
        $ips = new Collection;
        foreach ($device->getVrfContexts() as $context_name) {
            $ips = $ips->merge(SnmpQuery::walk([
                'IPV6-MIB::ipv6AddrPfxLength',
                'IPV6-MIB::ipv6AddrType',
            ])->mapTable(function ($data, $ipv6IfIndex = 0, $ipv6AddrAddress = '') use ($context_name, $device) {
                try {
                    $ip = IPv6::parse($ipv6AddrAddress);
                    $origin = match ($data['IP-MIB::ipv6AddrType'] ?? null) {
                        'stateless' => 'linklayer',
                        'stateful' => 'manual',
                        'unknown' => 'unknown',
                        default => 'other',
                    };

                    return new Ipv6Address([
                        'port_id' => PortCache::getIdFromIfIndex($ipv6IfIndex, $device),
                        'ipv6_address' => $ip->uncompressed(),
                        'ipv6_compressed' => $ip->compressed(),
                        'ipv6_prefixlen' => $data['IPV6-MIB::ipv6AddrPfxLength'] ?? '',
                        'ipv6_origin' => $origin,
                        'context_name' => $context_name,
                    ]);
                } catch (InvalidIpException $e) {
                    Log::error('Failed to parse IP: ' . $e->getMessage());

                    return null;
                }
            }));
        }

        return $ips->filter();
    }

    /**
     * @throws InvalidIpException
     */
    private function parseIp(string $ipAddressAddr, string $ifIndex): IP|IPv4|IPv6|null
    {
        // mis-formatted showing in dot notation
        if (str_contains($ipAddressAddr, '.')) {
            $cleanSnmpIp = implode('.', array_slice(explode('.', ltrim($ipAddressAddr, '.')), 0, 16));
            $ip = IPv6::fromSnmpString($cleanSnmpIp);
        } else {
            $cleanHexIp = str_replace(['"', "%$ifIndex"], '', $ipAddressAddr);
            $ip = IPv6::fromHexString($cleanHexIp);
        }

        return $ip;
    }

    private function parsePrefix(string $prefix): string
    {
        // prefix len is the last index of the ipAddressPrefixTable, fetch it from the pointer
        if (str_contains($prefix, '.')) {
            return substr($prefix, strrpos($prefix, '.') + 1);
        }

        preg_match('/(\d{1,3})]$/', $prefix, $prefix_match);

        return $prefix_match[1] ?? 0;
    }
}
