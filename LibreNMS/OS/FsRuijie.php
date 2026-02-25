<?php

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class FsRuijie extends OS implements TransceiverDiscovery
{
    public function discoverTransceivers(): Collection
    {
        return SnmpQuery::cache()->walk('FS-FIBER-MIB::fsFiberTable')->mapTable(function ($data, $ifIndex) {
            if (($data['FS-FIBER-MIB::fsFiberDDMSupportStatus'] ?? null) != 1) {
                return null;
            }

            $distance = null;
            $cable = null;

            if (! empty($data['FS-FIBER-MIB::fsFiberTransferDistanceSMF'])) {
                $distance = $data['FS-FIBER-MIB::fsFiberTransferDistanceSMF'] * 1000;
                $cable = 'SM';
            } elseif (! empty($data['FS-FIBER-MIB::fsFiberTransferDistance50umOM3'])) {
                $distance = $data['FS-FIBER-MIB::fsFiberTransferDistance50umOM3'];
                $cable = 'MM';
            } elseif (! empty($data['FS-FIBER-MIB::fsFiberTransferDistance50umOM2'])) {
                $distance = $data['FS-FIBER-MIB::fsFiberTransferDistance50umOM2'];
                $cable = 'MM';
            } elseif (! empty($data['FS-FIBER-MIB::fsFiberTransferDistance62point5umOM1'])) {
                $distance = $data['FS-FIBER-MIB::fsFiberTransferDistance62point5umOM1'];
                $cable = 'MM';
            } elseif (! empty($data['FS-FIBER-MIB::fsFiberTransferDistanceCopper'])) {
                $distance = $data['FS-FIBER-MIB::fsFiberTransferDistanceCopper'];
                $cable = 'Copper';
            }

            return new Transceiver([
                'port_id' => (int) PortCache::getIdFromIfIndex($ifIndex, $this->getDevice()),
                'index' => $ifIndex,
                'type' => $data['FS-FIBER-MIB::fsFiberTransceiverType'] ?? null,
                'serial' => $data['FS-FIBER-MIB::fsFiberSerialNumber'] ?? null,
                'cable' => $cable,
                'distance' => $distance,
                'wavelength' => $data['FS-FIBER-MIB::fsFiberWavelength'] ?? null,
                'entity_physical_index' => $ifIndex,
            ]);
        })->filter();
    }
}
