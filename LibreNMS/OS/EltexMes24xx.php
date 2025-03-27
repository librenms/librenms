<?php

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\EntPhysical;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;
use LibreNMS\OS\Traits\EntityMib;
use SnmpQuery;

class EltexMes24xx extends OS implements TransceiverDiscovery
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
}
