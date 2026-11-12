<?php

namespace LibreNMS\OS\Traits;

use App\Facades\PortCache;
use App\Models\Ipv4Address;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Exceptions\SnmpException;
use LibreNMS\Util\IPv4;
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
}
