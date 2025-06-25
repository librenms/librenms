<?php

namespace LibreNMS\OS;

use App\Models\EntPhysical;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\EntityPhysicalDiscovery;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;

class Neptune extends OS implements EntityPhysicalDiscovery, TransceiverDiscovery
{
    public function discoverEntityPhysical(): Collection
    {
        return \SnmpQuery::walk('NPT-SYSTEM-MIB::nptBoardInfoTable')
            ->mapTable(function ($entry, $index) {
                return new EntPhysical([
                    'entPhysicalIndex' => $index,
                    'entPhysicalDescr' => $entry['NPT-SYSTEM-MIB::nptCardDescription'],
                    'entPhysicalContainedIn' => 0,
                    'entPhysicalName' => $entry['NPT-SYSTEM-MIB::nptPhysicalBoardType'],
                    'entPhysicalSerialNum' => $entry['NPT-SYSTEM-MIB::nptCardSerialNumber'],
                    'entPhysicalModelName' => $entry['NPT-SYSTEM-MIB::nptLogicalBoardType'] ?? null,
                    'entPhysicalMfgName' => $entry['NPT-SYSTEM-MIB::nptVendor'],
                    'entPhysicalHardwareRev' => $entry['NPT-SYSTEM-MIB::nptHwRevision'],
                    'entPhysicalFirmwareRev' => $entry['NPT-SYSTEM-MIB::nptBootVersion'] ?: $entry['NPT-SYSTEM-MIB::nptFPGAVersion'],
                    'entPhysicalSoftwareRev' => $entry['NPT-SYSTEM-MIB::nptSWRevision'],
                    'entPhysicalAssetID' => $entry['NPT-SYSTEM-MIB::nptMacAddress'],
                ]);
            });
    }

    public function discoverTransceivers(): Collection
    {
        return \SnmpQuery::walk([
            'NPT-SYSTEM-MIB::nptTransceiverConfigurationTable',
            'NPT-SYSTEM-MIB::nptTransceiverStatusTable',
            'NPT-SYSTEM-MIB::nptTransceiverInventoryTable',
            ])
            ->mapTable(function ($entry, $index) {
                return new Transceiver([
                        'port_id' => \PortCache::getIdFromIfIndex($index),
                        'index' => $index,
                        'type' => $entry['NPT-SYSTEM-MIB::nptRate'],
                        'entity_physical_index' => $index,
                        'vendor' => $entry['NPT-SYSTEM-MIB::nptTransceiverInventoryVendor'],
                        'model' => $entry['NPT-SYSTEM-MIB::nptActualTransceiverType'],
                        'revision' => $entry['NPT-SYSTEM-MIB::nptHWRevision'],
                        'serial' => $entry['NPT-SYSTEM-MIB::nptSerialNumber'],
                        'cable' => $entry['NPT-SYSTEM-MIB::nptSupportedFiberType'],
                        'distance' => $entry['NPT-SYSTEM-MIB::nptActualSupportedLinkLength'],
                        'wavelength' => $entry['NPT-SYSTEM-MIB::nptActualTransmitedWavelength'],
                        'connector' => $entry['NPT-SYSTEM-MIB::nptConnectorType'],
                    ]);
            });
    }
}
