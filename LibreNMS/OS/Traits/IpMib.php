<?php

namespace LibreNMS\OS\Traits;

use App\Facades\PortCache;
use App\Models\Ipv4Address;
use App\Models\Ipv6Address;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Exceptions\SnmpException;
use LibreNMS\Util\IP;
use LibreNMS\Util\IPv4;
use LibreNMS\Util\IPv6;
use SnmpQuery;

trait IpMib
{
    public function discoverIpv4Addresses(): Collection
    {
        $device = $this->getDevice();
        $ips = new Collection;
        foreach ($device->getVrfContexts() as $context_name) {
            try {
                $response = SnmpQuery::context($context_name)->allowUnordered()->hideMib()->enumStrings()->walk(
                    ['IP-MIB::ipAdEntAddr', 'IP-MIB::ipAdEntIfIndex', 'IP-MIB::ipAdEntNetMask'])
                    ->throwIfConnectionError();
            } catch (SnmpException $e) {
                if ($context_name === '') {
                    throw $e;
                }

                Log::warning("SNMP context $context_name did not answer, its IPv4 addresses were not updated: " . $e->getMessage());
                $ips = $ips->merge($device->ipv4()->where('ipv4_addresses.context_name', $context_name)->get()
                    ->map(fn (Ipv4Address $ip) => new Ipv4Address($ip->only(['port_id', 'ipv4_address', 'ipv4_prefixlen', 'context_name']))));
                continue;
            }

            $ips = $ips->merge($response->mapTable(function ($data, $ipAddr = '') use ($context_name, $device) {
                //on some devices, ipAddr is broken, so use ipAdEntAddr as primary
                $entAddr = $data['ipAdEntAddr'] ?? '';
                $addr = (preg_match('/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$/', (string) $entAddr, $tmp)) ? $entAddr : $ipAddr;

                if (! IPv4::isValid($addr)) {
                    return null;
                }

                return new Ipv4Address([
                    'port_id' => PortCache::getIdFromIfIndex($data['ipAdEntIfIndex'] ?? 0, $device),
                    'ipv4_address' => $addr,
                    'ipv4_prefixlen' => $data['ipAdEntNetMask'] ?? '',
                    'context_name' => $context_name,
                ]);
            }));
        }

        return $ips->filter();
    }

    public function discoverIpv6Addresses(): Collection
    {
        $silentContexts = [];
        $ips = $this->discoverIpMibIpv6Addresses($silentContexts);
        if ($ips->isEmpty()) {
            $ips = $this->discoverIpv6MibAddresses();
        }

        return $ips->merge($this->getDevice()->ipv6()->whereIn('ipv6_addresses.context_name', $silentContexts)->get()
            ->map(fn (Ipv6Address $ip) => new Ipv6Address($ip->only(['port_id', 'ipv6_address', 'ipv6_compressed', 'ipv6_prefixlen', 'ipv6_origin', 'context_name']))));
    }

    /**
     * @param  string[]  $silentContexts  filled with the VRF contexts that did not answer
     */
    private function discoverIpMibIpv6Addresses(array &$silentContexts): Collection
    {
        $device = $this->getDevice();
        $ips = new Collection;
        foreach ($device->getVrfContexts() as $context_name) {
            try {
                $ips = $ips->merge($this->discoverIpMibIpv6ContextAddresses($context_name));
            } catch (SnmpException $e) {
                if ($context_name === '') {
                    throw $e;
                }

                Log::warning("SNMP context $context_name did not answer, its IPv6 addresses were not updated: " . $e->getMessage());
                $silentContexts[] = $context_name;
            }
        }

        return $ips->filter();
    }

    /**
     * @throws SnmpException when a walk of the context did not answer
     */
    private function discoverIpMibIpv6ContextAddresses(string $context_name): Collection
    {
        $device = $this->getDevice();
        $response = SnmpQuery::context($context_name)->enumStrings()->walk(['IP-MIB::ipAddressTable'])->throwIfConnectionError();
        $prefixOrigins = null; // only walked if a row needs it

        return $response->mapTable(function ($data, $ipAddressAddrType, $ipAddressAddr = '') use ($context_name, $device, &$prefixOrigins) {
            if (! in_array($ipAddressAddrType, ['ipv6', 'ipv6z'])) {
                return null;
            }

            try {
                Log::debug("Attempting to parse $ipAddressAddr");
                $ifIndex = $data['IP-MIB::ipAddressIfIndex'] ?? 0;
                $ip = $this->parseIpMibIpv6Address($ipAddressAddr);

                return new Ipv6Address([
                    'port_id' => PortCache::getIdFromIfIndex($ifIndex, $device),
                    'ipv6_address' => $ip->uncompressed(),
                    'ipv6_compressed' => $ip->compressed(),
                    'ipv6_prefixlen' => $this->parseIpMibIpv6PrefixLen($data, $ipAddressAddrType, $ip, $context_name, $prefixOrigins),
                    'ipv6_origin' => $data['IP-MIB::ipAddressOrigin'] ?? 'unknown',
                    'context_name' => $context_name,
                ]);
            } catch (InvalidIpException $e) {
                Log::error('Failed to parse IP: ' . $e->getMessage());

                return null;
            }
        });
    }

    // IPV6-MIB has no context, so its addresses belong to the default context
    private function discoverIpv6MibAddresses(): Collection
    {
        $device = $this->getDevice();

        return SnmpQuery::walk([
            'IPV6-MIB::ipv6AddrPfxLength',
            'IPV6-MIB::ipv6AddrType',
        ])->throwIfConnectionError()->mapTable(function ($data, $ipv6IfIndex = 0, $ipv6AddrAddress = '') use ($device) {
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
                    'context_name' => '',
                ]);
            } catch (InvalidIpException $e) {
                Log::error('Failed to parse IP: ' . $e->getMessage());

                return null;
            }
        })->filter();
    }

    /**
     * @throws InvalidIpException
     */
    private function parseIpMibIpv6Address(string $ipAddressAddr): IP|IPv4|IPv6|null
    {
        // mis-formatted showing in dot notation
        if (str_contains($ipAddressAddr, '.')) {
            $cleanSnmpIp = implode('.', array_slice(explode('.', ltrim($ipAddressAddr, '.')), 0, 16));

            return IPv6::fromSnmpString($cleanSnmpIp);
        }

        $cleanHexIp = trim(substr($ipAddressAddr, 0, strrpos($ipAddressAddr, '%') ?: null), '"');

        return IPv6::fromHexString($cleanHexIp);
    }

    private function parseIpMibIpv6PrefixLen(array $snmpRowData, string $ipAddressAddrType, IP $ip, string $context_name, ?array &$prefixOrigins): int
    {
        $prefix = $snmpRowData['IP-MIB::ipAddressPrefix'] ?? '';

        // prefix len is the last index of the ipAddressPrefixTable, fetch it from the pointer
        if (str_contains($prefix, '.')) {
            return (int) substr($prefix, strrpos($prefix, '.') + 1);
        }

        if (preg_match('/]\[(\d{1,3})]$/', $prefix, $prefix_match)) {
            return (int) $prefix_match[1];
        }

        // try IP-MIB::ipAddressPrefixTable to get the prefix length
        if (isset($snmpRowData['IP-MIB::ipAddressIfIndex'])) {
            $prefixOrigins ??= SnmpQuery::context($context_name)->cache()->walk('IP-MIB::ipAddressPrefixOrigin')->throwIfConnectionError()->table(4); // walk this and parse from index
            $longestPrefixLen = 0;
            foreach ($prefixOrigins[$snmpRowData['IP-MIB::ipAddressIfIndex']][$ipAddressAddrType] ?? [] as $prefix => $prefixData) {
                foreach (array_keys($prefixData) as $prefixLen) {
                    try {
                        // broken output, parse from raw index
                        if ($prefixLen === 'IP-MIB::ipAddressPrefixOrigin') {
                            if (preg_match('/^\.(?<ip>.+)\.(?<prefix_len>\d{1,3})$/', (string) $prefix, $prefix_match)) {
                                $prefix = $prefix_match['ip'];
                                $prefixLen = (int) $prefix_match['prefix_len'];
                            }
                        }

                        if ($ip->inNetwork($this->parseIpMibIpv6Address($prefix)->getNetwork($prefixLen))) {
                            $longestPrefixLen = max($longestPrefixLen, (int) $prefixLen);
                        }
                    } catch (InvalidIpException) {
                        // ignore failures, the result is 0 if no matches found
                        Log::debug("Failed to parse ipv6 prefix: $prefix");
                    }
                }
            }

            return $longestPrefixLen;
        }

        return 0;
    }
}
