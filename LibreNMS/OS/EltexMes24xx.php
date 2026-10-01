<?php

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\EntPhysical;
use App\Models\Ipv6Address;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Interfaces\Discovery\Ipv6AddressDiscovery;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;
use LibreNMS\OS\Traits\EntityMib;
use LibreNMS\Util\IPv6;
use SnmpQuery;

class EltexMes24xx extends OS implements Ipv6AddressDiscovery, TransceiverDiscovery
{
    use EntityMib {
        EntityMib::discoverEntityPhysical as discoverBaseEntityPhysical;
    }

    protected function useEntLogicalIndexAsIfIndex(): bool
    {
        return true;
    }

    public function discoverEntityPhysical(): Collection
    {
        $inventory = $this->discoverBaseEntityPhysical();

        // add SFPs
        $oidSfp = SnmpQuery::hideMib()->enumStrings()->cache()->walk('ELTEX-PHY-MIB::eltexPhyTransceiverInfoTable')->table(1);
        $ifIndexToEntIndexMap = array_flip($this->getIfIndexEntPhysicalMap());

        $moduleIndex = $inventory->where('entPhysicalClass', 'module')->value('entPhysicalIndex');
        foreach ($oidSfp as $ifIndex => $data) {
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => $ifIndexToEntIndexMap[$ifIndex] ?? 1000000 + $ifIndex,
                'entPhysicalSerialNum' => $data['eltexPhyTransceiverInfoSerialNumber'] ?? null,
                'entPhysicalModelName' => $data['eltexPhyTransceiverInfoPartNumber'] ?? null,
                'entPhysicalName' => $data['eltexPhyTransceiverInfoConnectorType'] ?? null,
                'entPhysicalDescr' => $data['eltexPhyTransceiverInfoType'] ?? null,
                'entPhysicalClass' => 'sfp-cage',
                'entPhysicalContainedIn' => $moduleIndex ?? 0,
                'entPhysicalMfgName' => $data['eltexPhyTransceiverInfoVendorName'] ?? null,
                'entPhysicalHardwareRev' => $data['eltexPhyTransceiverInfoVendorRevision'] ?? null,
                'entPhysicalIsFRU' => 'true',
                'entPhysicalParentRelPos' => $ifIndex,
                'ifIndex' => $ifIndex,
            ]));
        }

        return $inventory;
    }

    public function discoverTransceivers(): Collection
    {
        $ifIndexToEntIndexMap = array_flip($this->getIfIndexEntPhysicalMap());

        return SnmpQuery::hideMib()->enumStrings()->cache()->walk('ELTEX-PHY-MIB::eltexPhyTransceiverInfoTable')
            ->mapTable(function ($data, $ifIndex) use ($ifIndexToEntIndexMap) {
                if (($data['eltexPhyTransceiverInfoType'] ?? 'unknown') === 'unknown') {
                    return null;
                }

                return new Transceiver([
                    'port_id' => PortCache::getIdFromIfIndex($ifIndex, $this->getDevice()),
                    'index' => $ifIndex,
                    'connector' => ! empty($data['eltexPhyTransceiverInfoConnectorType']) ? strtoupper((string) $data['eltexPhyTransceiverInfoConnectorType']) : null,
                    'distance' => $data['eltexPhyTransceiverInfoTransferDistance'] ?? null,
                    'model' => $data['eltexPhyTransceiverInfoPartNumber'] ?? null,
                    'revision' => $data['eltexPhyTransceiverInfoVendorRevision'] ?? null,
                    'serial' => $data['eltexPhyTransceiverInfoSerialNumber'] ?? null,
                    'vendor' => $data['eltexPhyTransceiverInfoVendorName'] ?? null,
                    'wavelength' => $data['eltexPhyTransceiverInfoWaveLength'] ?? null,
                    'entity_physical_index' => $ifIndexToEntIndexMap[$ifIndex] ?? null,
                ]);
            })->filter();
    }

    public function discoverIpv6Addresses(): Collection
    {
        return SnmpQuery::allowUnordered()->enumStrings()->walk('IP-MIB::ipAddressPrefixTable')
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
                            'context_name' => '',
                        ]);
                    } catch (InvalidIpException $e) {
                        Log::error('Failed to parse IP: ' . $e->getMessage());

                        return null;
                    }
                }
            })->filter();
    }
}
