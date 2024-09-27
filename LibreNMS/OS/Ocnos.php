<?php

namespace LibreNMS\OS;

use App\Models\EntPhysical;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\EntityPhysicalDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Ocnos extends OS implements EntityPhysicalDiscovery
{
    private bool $sfpSeen = false;

    public function discoverEntityPhysical(): Collection
    {
        $inventory = new Collection;

        $stacks = SnmpQuery::walk('IPI-CMM-CHASSIS-MIB::cmmStackUnitTable')->table(1);
        SnmpQuery::walk('IPI-CMM-CHASSIS-MIB::cmmSysSwModuleTable')->table(1, $stacks); // software version
        foreach ($stacks as $cmmStackUnitIndex => $stack) {
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => $cmmStackUnitIndex,
                'entPhysicalDescr' => $this->describeChassis($stack),
                'entPhysicalClass' => 'chassis',
                'entPhysicalName' => $stack['IPI-CMM-CHASSIS-MIB::cmmStackUnitModelName'] ?? null,
                'entPhysicalModelName' => $stack['IPI-CMM-CHASSIS-MIB::cmmStackUnitPartNum'] ?? null,
                'entPhysicalSerialNum' => $stack['IPI-CMM-CHASSIS-MIB::cmmStackUnitSerialNumber'] ?? null,
                'entPhysicalContainedIn' => 0,
                'entPhysicalMfgName' => $stack['IPI-CMM-CHASSIS-MIB::cmmStackMfgName'] ?? null,
                'entPhysicalParentRelPos' => $cmmStackUnitIndex,
                'entPhysicalHardwareRev' => $stack['IPI-CMM-CHASSIS-MIB::cmmStackUnitSwitchChipRev'] ?? null,
                'entPhysicalSoftwareRev' => $stack['IPI-CMM-CHASSIS-MIB::cmmSysSwRuntimeImgVersion'] ?? null,
                'entPhysicalFirmwareRev' => $stack['IPI-CMM-CHASSIS-MIB::cmmStackOnieVersion'] ?? null,
                'entPhysicalIsFRU' => 'false',
            ]));
        }

        $psus = SnmpQuery::enumStrings()->walk('IPI-CMM-CHASSIS-MIB::cmmPsuFruTable')->table(2);
        foreach ($psus as $cmmStackUnitIndex => $chassisPsu) {
            foreach ($chassisPsu as $cmmSysPSUIndex => $psu) {
                $inventory->push(new EntPhysical([
                    'entPhysicalIndex' => $cmmStackUnitIndex * 1000 + $cmmSysPSUIndex,
                    'entPhysicalDescr' => (isset($psu['IPI-CMM-CHASSIS-MIB::cmmPsuType']) && $psu['IPI-CMM-CHASSIS-MIB::cmmPsuType'] != 'not-applicable') ? $psu['IPI-CMM-CHASSIS-MIB::cmmPsuType'] : null,
                    'entPhysicalClass' => 'powerSupply',
                    'entPhysicalName' => $psu['IPI-CMM-CHASSIS-MIB::cmmSysPowerSupplyType'] ?? null,
                    'entPhysicalModelName' => $psu['IPI-CMM-CHASSIS-MIB::cmmPsuPartNum'] ?? null,
                    'entPhysicalSerialNum' => $psu['IPI-CMM-CHASSIS-MIB::cmmPsuSerialNumber'] ?? null,
                    'entPhysicalContainedIn' => $cmmStackUnitIndex,
                    'entPhysicalMfgName' => $psu['IPI-CMM-CHASSIS-MIB::cmmPsuManufactureId'] ?? null,
                    'entPhysicalParentRelPos' => $cmmSysPSUIndex,
                    'entPhysicalHardwareRev' => $psu['IPI-CMM-CHASSIS-MIB::cmmPsuPartNumRev'] ?? null,
                    'entPhysicalIsFRU' => 'true',
                ]));
            }
        }

        $fans = SnmpQuery::enumStrings()->walk('IPI-CMM-CHASSIS-MIB::cmmFanFruTable')->table(3);
        foreach ($fans as $cmmStackUnitIndex => $chassisFans) {
            foreach ($chassisFans as $cmmFanTrayNumber => $fan) {
                $inventory->push(new EntPhysical([
                    'entPhysicalIndex' => $cmmStackUnitIndex * 1000 + 100 + $cmmFanTrayNumber,
                    'entPhysicalDescr' => isset($psu['IPI-CMM-CHASSIS-MIB::cmmFanNumOfFanPerTray']) ? "Fan Tray with {$psu['IPI-CMM-CHASSIS-MIB::cmmFanNumOfFanPerTray']} Fan(s)" : null,
                    'entPhysicalClass' => 'fan',
                    'entPhysicalName' => $psu['IPI-CMM-CHASSIS-MIB::cmmFanType'] ?? null,
                    'entPhysicalModelName' => $psu['IPI-CMM-CHASSIS-MIB::cmmFanPartNum'] ?? null,
                    'entPhysicalSerialNum' => $psu['IPI-CMM-CHASSIS-MIB::cmmFanSerialNumber'] ?? null,
                    'entPhysicalContainedIn' => $cmmStackUnitIndex,
                    'entPhysicalMfgName' => $psu['IPI-CMM-CHASSIS-MIB::cmmFanManufactureId'] ?? null,
                    'entPhysicalParentRelPos' => $cmmFanTrayNumber,
                    'entPhysicalHardwareRev' => $psu['IPI-CMM-CHASSIS-MIB::cmmFanPartNumRev'] ?? null,
                    'entPhysicalIsFRU' => 'true',
                ]));
            }
        }

        $transceivers = SnmpQuery::enumStrings()->walk('IPI-CMM-CHASSIS-MIB::cmmTransEEPROMTable')->table(2);
        foreach ($transceivers as $cmmStackUnitIndex => $chassisTransceivers) {
            foreach ($chassisTransceivers as $cmmTransIndex => $transceiver) {
                $inventory->push(new EntPhysical([
                    'entPhysicalIndex' => $cmmStackUnitIndex * 10000 + $cmmTransIndex,
                    'entPhysicalDescr' => $this->describeTransceiver($transceiver),
                    'entPhysicalClass' => 'module',
                    'entPhysicalName' => $transceiver['IPI-CMM-CHASSIS-MIB::cmmTransVendorPartNumber'] ?? null,
                    'entPhysicalModelName' => $transceiver['IPI-CMM-CHASSIS-MIB::cmmTransVendorPartNumber'] ?? null,
                    'entPhysicalSerialNum' => $transceiver['IPI-CMM-CHASSIS-MIB::cmmTransVendorSerialNumber'] ?? null,
                    'entPhysicalContainedIn' => $cmmStackUnitIndex,
                    'entPhysicalMfgName' => $transceiver['IPI-CMM-CHASSIS-MIB::cmmTransVendorName'] ?? null,
                    'entPhysicalParentRelPos' => $cmmTransIndex,
                    'entPhysicalHardwareRev' => $transceiver['IPI-CMM-CHASSIS-MIB::cmmTransVendorRevision'] ?? null,
                    'entPhysicalIsFRU' => 'true',
                    'ifIndex' => $this->guessPortId($cmmTransIndex, $transceiver['IPI-CMM-CHASSIS-MIB::cmmTransType'] ?? 'missing'),
                ]));
            }
        }

        return $inventory;
    }

    private function describeChassis(array $stack): string
    {
        $description = $stack['IPI-CMM-CHASSIS-MIB::cmmStackUnitModelName'] ?? '';

        $items = [
            'IPI-CMM-CHASSIS-MIB::cmmStackUnitNum100GigEtherPorts' => '100G',
            'IPI-CMM-CHASSIS-MIB::cmmStackUnitNum50GigEtherPorts' => '50G',
            'IPI-CMM-CHASSIS-MIB::cmmStackUnitNum40GigEtherPorts' => '40G',
            'IPI-CMM-CHASSIS-MIB::cmmStackUnitNum25GigEtherPorts' => '25G',
            'IPI-CMM-CHASSIS-MIB::cmmStackUnitNum10GigEtherPorts' => '10G',
            'IPI-CMM-CHASSIS-MIB::cmmStackUnitNumGigEtherPorts' => '1G',
            'IPI-CMM-CHASSIS-MIB::cmmStackUnitNumFastEtherPorts' => '100M',
            'IPI-CMM-CHASSIS-MIB::cmmStackUnitNumPluggableModules' => 'Pluggable',
            'IPI-CMM-CHASSIS-MIB::cmmStackUnitNumPowerSupplies' => 'PSU',
            'IPI-CMM-CHASSIS-MIB::cmmStackUnitNumFanControllers' => 'FCU',
            'IPI-CMM-CHASSIS-MIB::cmmStackUnitNumFanTrays' => 'Fans',
        ];

        foreach ($items as $oid => $label) {
            $value = $stack[$oid] ?? -100001;
            if ($value > 0) {
                $description .= " $stack[$oid]x$label";
            }
        }

        return $description;
    }

    private function describeTransceiver(array $transceiver): string
    {
        $description = $transceiver['IPI-CMM-CHASSIS-MIB::cmmTransType'] ?? '';

        if (isset($transceiver['IPI-CMM-CHASSIS-MIB::cmmTransExtEthCompliance']) && $transceiver['IPI-CMM-CHASSIS-MIB::cmmTransExtEthCompliance'] != 'unavailable') {
            $description .= ' ' . str_replace('eec-', '', $transceiver['IPI-CMM-CHASSIS-MIB::cmmTransExtEthCompliance']);
        } elseif (isset($transceiver['IPI-CMM-CHASSIS-MIB::cmmTransEthCompliance']) && $transceiver['IPI-CMM-CHASSIS-MIB::cmmTransEthCompliance'] != 'unavailable') {
            $description .= ' ' . str_replace('ec-', '', $transceiver['IPI-CMM-CHASSIS-MIB::cmmTransEthCompliance']);
        }

        if (isset($transceiver['IPI-CMM-CHASSIS-MIB::cmmTransLengthKmtrs']) && $transceiver['IPI-CMM-CHASSIS-MIB::cmmTransLengthKmtrs'] > 0) {
            $description .= ' ' . $transceiver['IPI-CMM-CHASSIS-MIB::cmmTransLengthKmtrs'] . 'km';
        } elseif (isset($transceiver['IPI-CMM-CHASSIS-MIB::cmmTransLengthMtrs']) && $transceiver['IPI-CMM-CHASSIS-MIB::cmmTransLengthMtrs'] > 0) {
            $description .= ' ' . $transceiver['IPI-CMM-CHASSIS-MIB::cmmTransLengthMtrs'] . 'm';
        } elseif (isset($transceiver['IPI-CMM-CHASSIS-MIB::cmmTransLengthOM4']) && $transceiver['IPI-CMM-CHASSIS-MIB::cmmTransLengthOM4'] > 0) {
            $description .= ' ' . $transceiver['IPI-CMM-CHASSIS-MIB::cmmTransLengthOM4'] . 'm';
        } elseif (isset($transceiver['IPI-CMM-CHASSIS-MIB::cmmTransLengthOM3']) && $transceiver['IPI-CMM-CHASSIS-MIB::cmmTransLengthOM3'] > 0) {
            $description .= ' ' . $transceiver['IPI-CMM-CHASSIS-MIB::cmmTransLengthOM3'] . 'm';
        } elseif (isset($transceiver['IPI-CMM-CHASSIS-MIB::cmmTransLengthOM2']) && $transceiver['IPI-CMM-CHASSIS-MIB::cmmTransLengthOM2'] > 0) {
            $description .= ' ' . $transceiver['IPI-CMM-CHASSIS-MIB::cmmTransLengthOM2'] . 'm';
        } elseif (isset($transceiver['IPI-CMM-CHASSIS-MIB::cmmTransLengthOM1']) && $transceiver['IPI-CMM-CHASSIS-MIB::cmmTransLengthOM1'] > 0) {
            $description .= ' ' . $transceiver['IPI-CMM-CHASSIS-MIB::cmmTransLengthOM1'] . 'm';
        }

        return $description;
    }

    private function guessPortId($cmmTransIndex, $cmmTransType): int
    {
        // IP Infusion has no reliable way of mapping a transceiver to a port it varies by hardware

        $prefix = match ($cmmTransType) {
            'sfp' => 'xe',
            'qsfp' => 'ce',
            default => 'ge',
        };

        // Handle UfiSpace S9600 10G breakout, which is optionally enabled
        if ($cmmTransType == 'sfp') {
            $this->sfpSeen = true;
        }

        $portName = match ($this->getDevice()->hardware) {
            'Ufi Space S9600-32X-R' => $prefix . ($this->sfpSeen ? ($cmmTransType == 'qsfp' ? $cmmTransIndex - 5 : $cmmTransIndex - 2) : $cmmTransIndex - 1),
            'Ufi Space S9510-28DC-B' => $prefix . ($cmmTransIndex - 1),
            'Ufi Space S9500-30XS-P' => $prefix . ($cmmTransType == 'qsfp' ? $cmmTransIndex - 29 : $cmmTransIndex - 1),
            'Edgecore 7316-26XB-O-48V-F' => $prefix . ($cmmTransType == 'qsfp' ? $cmmTransIndex - 1 : $cmmTransIndex - 3),
            'Edgecore 5912-54X-O-AC-F' => $prefix . $cmmTransIndex,
            'Edgecore 7712-32X-O-AC-F' => $prefix . $cmmTransIndex . '/1',
            default => null, // no port map, so we can't guess
        };

        if ($portName === null) {
            return 0; // give up
        }

        // load port name to port_id map
        $ifNameToIndex = array_flip(SnmpQuery::cache()->walk('IF-MIB::ifName')->pluck());

        return $ifNameToIndex[$portName] ?? 0;
    }
}
