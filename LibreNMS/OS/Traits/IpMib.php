<?php

namespace LibreNMS\OS\Traits;

use App\Facades\PortCache;
use App\Models\Ipv4Address;
use Illuminate\Support\Collection;
use LibreNMS\Util\IPv4;
use SnmpQuery;

trait IpMib
{
    public function discoverIpv4Addresses(): Collection
    {
        $device = $this->getDevice();
        $ips = new Collection;
        foreach ($device->getVrfContexts() as $context_name) {
            $ips = $ips->merge(SnmpQuery::context($context_name)->allowUnordered()->hideMib()->enumStrings()->walk(
                ['IP-MIB::ipAdEntAddr', 'IP-MIB::ipAdEntIfIndex', 'IP-MIB::ipAdEntNetMask'])
            ->mapTable(function ($data, $ipAddr = '') use ($context_name, $device) {
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
