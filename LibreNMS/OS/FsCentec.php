<?php

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class FsCentec extends OS implements TransceiverDiscovery
{
    public function discoverTransceivers(): Collection
    {
        return SnmpQuery::cache()->walk('FS-SWITCH-V2-MIB::transbasicinformationTable')->mapTable(function ($data, $ifIndex) {
            if ($data['FS-SWITCH-V2-MIB::transceiveStatus'] == 'inactive') {
                return null;
            }

            $distance = null;
            $cable = null;
            if (isset($data['FS-SWITCH-V2-MIB::link9SinglemodeLengthKm']) && $data['FS-SWITCH-V2-MIB::link9SinglemodeLengthKm'] != 0) {
                $distance = $data['FS-SWITCH-V2-MIB::link9SinglemodeLengthKm'] * 1000;
                $cable = 'SM';
            } elseif (isset($data['FS-SWITCH-V2-MIB::link9SinglemodeLengthM']) && $data['FS-SWITCH-V2-MIB::link9SinglemodeLengthM'] != 0) {
                $distance = $data['FS-SWITCH-V2-MIB::link9SinglemodeLengthM'];
                $cable = 'SM';
            } elseif (isset($data['FS-SWITCH-V2-MIB::link50MultimodeLength']) && $data['FS-SWITCH-V2-MIB::link50MultimodeLength'] != 0) {
                $distance = $data['FS-SWITCH-V2-MIB::link50MultimodeLength'];
                $cable = 'MM';
            } elseif (isset($data['FS-SWITCH-V2-MIB::link62MultimodeLength']) && $data['FS-SWITCH-V2-MIB::link62MultimodeLength'] != 0) {
                $distance = $data['FS-SWITCH-V2-MIB::link62MultimodeLength'];
                $cable = 'MM';
            } elseif (isset($data['FS-SWITCH-V2-MIB::linkCopperLength']) && $data['FS-SWITCH-V2-MIB::linkCopperLength'] != 0) {
                $distance = $data['FS-SWITCH-V2-MIB::linkCopperLength'];
                $cable = 'Copper';
            }

            return new Transceiver([
                'port_id' => (int) PortCache::getIdFromIfIndex($ifIndex, $this->getDevice()),
                'index' => $ifIndex,
                'vendor' => $data['FS-SWITCH-V2-MIB::transceiveVender'] ?? null,
                'type' => $data['FS-SWITCH-V2-MIB::transceiveType'] ?? null,
                'model' => $data['FS-SWITCH-V2-MIB::transceivePartNumber'] ?? null,
                'serial' => $data['FS-SWITCH-V2-MIB::transceiveSerialNumber'] ?? null,
                'cable' => $cable,
                'distance' => $distance,
                'wavelength' => $data['FS-SWITCH-V2-MIB::transceiveWaveLength'] ?? null,
                'entity_physical_index' => $ifIndex,
            ]);
        })->filter();
    }
}
