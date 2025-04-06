<?php

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Ipv6Address;
use App\Models\EntPhysical;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Interfaces\Discovery\Ipv6AddressDiscovery;
use LibreNMS\OS;
use LibreNMS\OS\Traits\EntityMib;
use LibreNMS\Util\IPv6;
use SnmpQuery;

class EltexMes24xx extends OS implements TransceiverDiscovery,Ipv6AddressDiscovery

{
    use EntityMib {
        EntityMib::discoverEntityPhysical as discoverBaseEntityPhysical;
    }

    public function discoverEntityPhysical(): Collection
    {
        $inventory = $this->discoverBaseEntityPhysical();

        // add SFPs
        $oidSfp = SnmpQuery::hideMib()->enumStrings()->cache()->walk('ELTEX-PHY-MIB::eltexPhyTransceiverInfoTable')->table(1);
        $ifIndexToEntIndexMap = array_flip($this->getIfIndexEntPhysicalMap());

        foreach ($oidSfp as $ifIndex => $data) {
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 1000000 + $ifIndex,
                'entPhysicalSerialNum' => $data['eltexPhyTransceiverInfoSerialNumber'],
                'entPhysicalModelName' => $data['eltexPhyTransceiverInfoPartNumber'],
                'entPhysicalName' => $data['eltexPhyTransceiverInfoConnectorType'],
                'entPhysicalDescr' => $data['eltexPhyTransceiverInfoType'],
                'entPhysicalClass' => 'sfp-cage',
                'entPhysicalContainedIn' => $ifIndexToEntIndexMap[$ifIndex] ?? 0,
                'entPhysicalMfgName' => $data['eltexPhyTransceiverInfoVendorName'],
                'entPhysicalHardwareRev' => $data['eltexPhyTransceiverInfoVendorRevision'],
                'entPhysicalIsFRU' => 'true',
                'ifIndex' => $ifIndex,
            ]));
        }

        return $inventory;
    }

    public function discoverTransceivers(): Collection
    {
        return SnmpQuery::hideMib()->enumStrings()->cache()->walk('ELTEX-PHY-MIB::eltexPhyTransceiverInfoTable')
            ->mapTable(function ($data, $ifIndex) {
                return new Transceiver([
                    'port_id' => PortCache::getIdFromIfIndex($ifIndex, $this->getDevice()),
                    'index' => $ifIndex,
                    'connector' => $data['eltexPhyTransceiverInfoConnectorType'] ? strtoupper($data['eltexPhyTransceiverInfoConnectorType']) : null,
                    'distance' => $data['eltexPhyTransceiverInfoTransferDistance'] ?? null,
                    'model' => $data['eltexPhyTransceiverInfoPartNumber'] ?? null,
                    'revision' => $data['eltexPhyTransceiverInfoVendorRevision'] ?? null,
                    'serial' => $data['eltexPhyTransceiverInfoSerialNumber'] ?? null,
                    'vendor' => $data['eltexPhyTransceiverInfoVendorName'] ?? null,
                    'wavelength' => $data['eltexPhyTransceiverInfoWaveLength'] ?? null,
                    'entity_physical_index' => $ifIndex,
                ]);
            });
    }

    public function discoverIpv6Addresses(): Collection
    {
        return \SnmpQuery::allowUnordered()->enumStrings()->walk('IP-MIB::ipAddressPrefixTable')
            ->mapTable(function ($data, $ifIndex, $addrType, $address, $prefixLen) {
                if ($addrType == 'ipv6') {
                    try {
                        $ip = IPv6::fromHexString($address);

                        return new Ipv6Address([
                            'ipv6_address' => $ip->uncompressed(),
                            'ipv6_compressed' => $ip->compressed(),
                            'ipv6_prefixlen' => $prefixLen ?? '',
                            'ipv6_origin' => $data['IP-MIB::ipAddressPrefixOrigin'] ?? 'unknown',
                            'port_id' => PortCache::getIdFromIfIndex($ifIndex, $this->getDevice()),
                        ]);
                    } catch (InvalidIpException $e) {
                        Log::error('Failed to parse IP: ' . $e->getMessage());

                        return null;
                    }
                }
            })->filter();
    }
}
