<?php

/**
 * Nokia1830.php
 *
 * Nokia 1830 PSS (Photonic Service Switch) OS
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Nick Peelman
 * @author     Nick Peelman <nick@peelman.us>
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\EntPhysical;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\EntityPhysicalDiscovery;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Nokia1830 extends OS implements EntityPhysicalDiscovery, TransceiverDiscovery
{
    /**
     * Discover entity physical inventory for Nokia 1830 PSS/PSD devices
     *
     * Uses TROPIC MIBs:
     * - TROPIC-SHELF-MIB for shelf/chassis information
     * - TROPIC-CARD-MIB for cards/modules
     * - TROPIC-FAN-MIB for fan units
     * - TROPIC-PSD-MIB for PSD-specific inventory
     */
    public function discoverEntityPhysical(): Collection
    {
        $inventory = new Collection;

        // Try PSS shelf discovery first (TROPIC-SHELF-MIB)
        $shelves = SnmpQuery::walk('TROPIC-SHELF-MIB::tnShelfRiDataTable')->table(1);
        $shelfBaseInfo = SnmpQuery::walk([
            'TROPIC-SHELF-MIB::tnShelfName',
            'TROPIC-SHELF-MIB::tnShelfSerialNumber',
        ])->table(1);

        foreach ($shelves as $shelfIndex => $shelf) {
            $baseInfo = $shelfBaseInfo[$shelfIndex] ?? [];
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => $shelfIndex,
                'entPhysicalDescr' => $shelf['TROPIC-SHELF-MIB::tnShelfRiMnemonic'] ?? null,
                'entPhysicalClass' => 'chassis',
                'entPhysicalName' => $baseInfo['TROPIC-SHELF-MIB::tnShelfName'] ?? "Shelf $shelfIndex",
                'entPhysicalModelName' => $shelf['TROPIC-SHELF-MIB::tnShelfRiManufacturingPartNumber'] ?? null,
                'entPhysicalSerialNum' => $shelf['TROPIC-SHELF-MIB::tnShelfRiSerialNumber']
                    ?? $baseInfo['TROPIC-SHELF-MIB::tnShelfSerialNumber'] ?? null,
                'entPhysicalContainedIn' => 0,
                'entPhysicalMfgName' => $shelf['TROPIC-SHELF-MIB::tnShelfRiCompanyID'] ?? 'Nokia',
                'entPhysicalVendorType' => $shelf['TROPIC-SHELF-MIB::tnShelfRiCLEI'] ?? null,
                'entPhysicalParentRelPos' => $shelfIndex,
                'entPhysicalIsFRU' => 'false',
            ]));
        }

        // If no PSS shelves found, try PSD shelf discovery (TROPIC-PSD-MIB)
        if ($inventory->isEmpty()) {
            $psdShelves = SnmpQuery::walk('TROPIC-PSD-MIB::tnPsdShelfTable')->table(1);
            foreach ($psdShelves as $shelfIndex => $shelf) {
                $inventory->push(new EntPhysical([
                    'entPhysicalIndex' => $shelfIndex,
                    'entPhysicalDescr' => $shelf['TROPIC-PSD-MIB::tnPsdShelfDescr'] ?? null,
                    'entPhysicalClass' => 'chassis',
                    'entPhysicalName' => $shelf['TROPIC-PSD-MIB::tnPsdShelfName'] ?? "Shelf $shelfIndex",
                    'entPhysicalContainedIn' => 0,
                    'entPhysicalMfgName' => 'Nokia',
                    'entPhysicalParentRelPos' => $shelfIndex,
                    'entPhysicalIsFRU' => 'false',
                ]));
            }
        }

        // If still no shelves, create a virtual chassis entry
        if ($inventory->isEmpty()) {
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 1,
                'entPhysicalDescr' => 'Nokia 1830 Chassis',
                'entPhysicalClass' => 'chassis',
                'entPhysicalName' => 'Chassis 1',
                'entPhysicalContainedIn' => 0,
                'entPhysicalMfgName' => 'Nokia',
                'entPhysicalParentRelPos' => 1,
                'entPhysicalIsFRU' => 'false',
            ]));
        }

        // Discover cards from TROPIC-CARD-MIB (PSS systems)
        $cards = SnmpQuery::walk('TROPIC-CARD-MIB::tnCardTable')->table(2);
        foreach ($cards as $shelfIndex => $shelfCards) {
            foreach ($shelfCards as $slotIndex => $card) {
                // Generate unique index: shelf * 1000 + slot
                $cardIndex = ($shelfIndex * 1000) + $slotIndex;

                // tnCardMnemonic contains the product type (e.g., "IR9", "8DC30", "S13X100R")
                // tnCardName contains user-assigned name (e.g., "Power Filter-1-A", "degree-a")
                // tnCardManufacturingPartNumber contains Nokia part number (e.g., "3KC70869AAAA01")
                $mnemonic = trim($card['TROPIC-CARD-MIB::tnCardMnemonic'] ?? '');
                $cardName = trim($card['TROPIC-CARD-MIB::tnCardName'] ?? '');
                $partNumber = trim($card['TROPIC-CARD-MIB::tnCardManufacturingPartNumber'] ?? '');

                // Build a descriptive name: prefer mnemonic (product type), fallback to card name
                $displayName = $mnemonic ?: $cardName ?: "Slot $slotIndex";

                // Description: combine mnemonic and user name if both exist
                $descr = '';
                if ($mnemonic && $cardName && $mnemonic !== $cardName) {
                    $descr = "$mnemonic ($cardName)";
                } elseif ($mnemonic) {
                    $descr = $mnemonic;
                } elseif ($cardName) {
                    $descr = $cardName;
                }

                $inventory->push(new EntPhysical([
                    'entPhysicalIndex' => $cardIndex,
                    'entPhysicalDescr' => $descr ?: null,
                    'entPhysicalClass' => 'module',
                    'entPhysicalName' => $displayName,
                    'entPhysicalModelName' => $partNumber ?: ($card['TROPIC-CARD-MIB::tnCardMarketingPartNumber'] ?? null),
                    'entPhysicalSerialNum' => $card['TROPIC-CARD-MIB::tnCardSerialNumber'] ?? null,
                    'entPhysicalContainedIn' => $shelfIndex,
                    'entPhysicalMfgName' => $card['TROPIC-CARD-MIB::tnCardCompanyID'] ?? 'Nokia',
                    'entPhysicalVendorType' => $card['TROPIC-CARD-MIB::tnCardCLEI'] ?? null,
                    'entPhysicalParentRelPos' => $slotIndex,
                    'entPhysicalSoftwareRev' => $card['TROPIC-CARD-MIB::tnCardSWGenericLoadName'] ?? null,
                    'entPhysicalFirmwareRev' => $card['TROPIC-CARD-MIB::tnCardLastBootedFwBundleVer'] ?? null,
                    'entPhysicalIsFRU' => 'true',
                ]));
            }
        }

        // Discover PSD cards if no PSS cards found (TROPIC-PSD-MIB)
        if ($cards === []) {
            $psdCards = SnmpQuery::walk('TROPIC-PSD-MIB::tnPsdCardTable')->table(2);
            foreach ($psdCards as $shelfIndex => $shelfCards) {
                foreach ($shelfCards as $slotIndex => $card) {
                    $cardIndex = ($shelfIndex * 1000) + $slotIndex;

                    $inventory->push(new EntPhysical([
                        'entPhysicalIndex' => $cardIndex,
                        'entPhysicalDescr' => $card['TROPIC-PSD-MIB::tnPsdCardMnemonic'] ?? null,
                        'entPhysicalClass' => 'module',
                        'entPhysicalName' => $card['TROPIC-PSD-MIB::tnPsdCardMnemonic'] ?? "Slot $slotIndex",
                        'entPhysicalModelName' => $card['TROPIC-PSD-MIB::tnPsdCardUnitPartNumber'] ?? null,
                        'entPhysicalSerialNum' => $card['TROPIC-PSD-MIB::tnPsdCardSerialNumber'] ?? null,
                        'entPhysicalContainedIn' => $shelfIndex,
                        'entPhysicalMfgName' => $card['TROPIC-PSD-MIB::tnPsdCardCompanyID'] ?? 'Nokia',
                        'entPhysicalVendorType' => $card['TROPIC-PSD-MIB::tnPsdCardCLEI'] ?? null,
                        'entPhysicalParentRelPos' => $slotIndex,
                        'entPhysicalIsFRU' => 'true',
                    ]));
                }
            }
        }

        // Discover fan units from TROPIC-FAN-MIB
        $fans = SnmpQuery::walk('TROPIC-FAN-MIB::tnFanUnitTable')->table(2);
        foreach ($fans as $shelfIndex => $shelfFans) {
            foreach ($shelfFans as $fanIndex => $fan) {
                // Generate unique index: 100000 + shelf * 1000 + fan
                $fanPhysIndex = 100000 + ($shelfIndex * 1000) + $fanIndex;

                $inventory->push(new EntPhysical([
                    'entPhysicalIndex' => $fanPhysIndex,
                    'entPhysicalDescr' => $fan['TROPIC-FAN-MIB::tnFanUnitDescr'] ?? null,
                    'entPhysicalClass' => 'fan',
                    'entPhysicalName' => $fan['TROPIC-FAN-MIB::tnFanUnitName'] ?? "Fan $fanIndex",
                    'entPhysicalModelName' => $fan['TROPIC-FAN-MIB::tnFanUnitManufacturingPartNumber']
                        ?? $fan['TROPIC-FAN-MIB::tnFanUnitMarketingPartNumber'] ?? null,
                    'entPhysicalSerialNum' => $fan['TROPIC-FAN-MIB::tnFanUnitSerialNumber'] ?? null,
                    'entPhysicalContainedIn' => $shelfIndex,
                    'entPhysicalVendorType' => $fan['TROPIC-FAN-MIB::tnFanUnitCLEI'] ?? null,
                    'entPhysicalParentRelPos' => $fanIndex,
                    'entPhysicalSoftwareRev' => $fan['TROPIC-FAN-MIB::tnFanUnitSWGenericLoadName'] ?? null,
                    'entPhysicalIsFRU' => 'true',
                ]));
            }
        }

        return $inventory;
    }

    /**
     * Discover transceivers for Nokia 1830 PSS/PSD devices using TROPIC MIBs
     *
     * Uses TROPIC-OPTICALPORT-MIB::tnSfpPortInfoTable for PSS SFP/XFP module information
     * Uses TROPIC-PSD-MIB::tnPsdSfpInfoTable for PSD SFP module information
     */
    public function discoverTransceivers(): Collection
    {
        // Try PSS SFP discovery first
        $transceivers = SnmpQuery::enumStrings()->walk([
            'TROPIC-OPTICALPORT-MIB::tnSfpPortModulePresentType',
            'TROPIC-OPTICALPORT-MIB::tnSfpPortModuleVendorSerNo',
            'TROPIC-OPTICALPORT-MIB::tnSfpPortModuleVendor',
            'TROPIC-OPTICALPORT-MIB::tnSfpPortWavelength',
            'TROPIC-OPTICALPORT-MIB::tnSfpPortModuleType',
            'TROPIC-OPTICALPORT-MIB::tnSfpPortCLEI',
            'TROPIC-OPTICALPORT-MIB::tnSfpPortUnitPartNum',
            'TROPIC-OPTICALPORT-MIB::tnSfpPortDate',
            'TROPIC-OPTICALPORT-MIB::tnSfpPortAcronymCode',
            'TROPIC-OPTICALPORT-MIB::tnSfpPortPowerClass',
            'TROPIC-OPTICALPORT-MIB::tnSfpPortFirmwareVersion',
        ])->mapTable(function ($data, $ifIndex) {
            // Check if SFP is present by looking for serial number or vendor
            // Empty serial and vendor means no SFP module installed in this slot
            $serial = trim($data['TROPIC-OPTICALPORT-MIB::tnSfpPortModuleVendorSerNo'] ?? '');
            $vendor = trim($data['TROPIC-OPTICALPORT-MIB::tnSfpPortModuleVendor'] ?? '');

            // Skip ports without SFP module present (no serial or vendor info)
            if (empty($serial) && empty($vendor)) {
                return null;
            }

            $portId = PortCache::getIdFromIfIndex((int) $ifIndex, $this->getDevice());
            if ($portId === null) {
                return null;
            }

            // Get module type from the module type string or acronym code
            $moduleType = $data['TROPIC-OPTICALPORT-MIB::tnSfpPortModuleType'] ?? null;
            $acronymCode = $data['TROPIC-OPTICALPORT-MIB::tnSfpPortAcronymCode'] ?? null;

            // Determine transceiver type from module type or acronym
            $type = $this->mapTransceiverType($moduleType, $acronymCode);

            // Get wavelength (in nm)
            $wavelength = $data['TROPIC-OPTICALPORT-MIB::tnSfpPortWavelength'] ?? null;
            if ($wavelength !== null) {
                $wavelength = (int) $wavelength;
                // Convert from pm to nm if needed (values > 2000 are likely pm)
                if ($wavelength > 2000) {
                    $wavelength = (int) round($wavelength / 1000);
                }
            }

            // Parse manufacture date if available
            // tnSfpPortDate is a SnmpAdminString with date info
            $date = $this->parseDate($data['TROPIC-OPTICALPORT-MIB::tnSfpPortDate'] ?? null);

            return new Transceiver([
                'port_id' => $portId,
                'index' => (string) $ifIndex,
                'entity_physical_index' => (int) $ifIndex,
                'type' => $type,
                'vendor' => $vendor ?: null,
                'model' => $this->cleanString($data['TROPIC-OPTICALPORT-MIB::tnSfpPortUnitPartNum'] ?? null),
                'serial' => $serial ?: null,
                'revision' => $this->cleanString($data['TROPIC-OPTICALPORT-MIB::tnSfpPortFirmwareVersion'] ?? null),
                'date' => $date,
                'wavelength' => $wavelength > 0 ? $wavelength : null,
            ]);
        })->filter();

        // If no SFP table data, try to infer transceivers from amplifier/iroadm port data (PSS)
        if ($transceivers->isEmpty()) {
            $transceivers = $this->discoverPssTransceiversFromAmplifierPorts();
        }

        // If still empty, try PSD SFP discovery
        if ($transceivers->isEmpty()) {
            $transceivers = $this->discoverPsdTransceivers();
        }

        return $transceivers;
    }

    /**
     * Discover transceivers for PSS devices from amplifier/iroadm port data
     *
     * When tnSfpPortInfoTable is not available, we can infer transceiver presence
     * from tnAmplifierPortInfoTable or tnIroadmPortInfoTable OSC data
     */
    private function discoverPssTransceiversFromAmplifierPorts(): Collection
    {
        $ifNames = SnmpQuery::cache()->walk('IF-MIB::ifName')->pluck();

        // Check for ports with OSC SFP data (indicates transceiver presence)
        // tnAmplifierPortInfoOSCSfpTxPowerOut (.23) and tnAmplifierPortInfoOSCSfpRxPowerIn (.24)
        $oscSfpTx = SnmpQuery::walk('TROPIC-AMPLIFIER-MIB::tnAmplifierPortInfoOSCSfpTxPowerOut')->pluck();
        $oscSfpRx = SnmpQuery::walk('TROPIC-AMPLIFIER-MIB::tnAmplifierPortInfoOSCSfpRxPowerIn')->pluck();

        // Merge all ports that have OSC SFP data
        $portsWithTransceivers = array_unique(array_merge(
            array_keys($oscSfpTx),
            array_keys($oscSfpRx)
        ));

        // Also check iroadm ports
        $iroadmOscTx = SnmpQuery::walk('TROPIC-OCH-MIB::tnIroadmPortInfoOSCSfpTxPowerOut')->pluck();
        $iroadmOscRx = SnmpQuery::walk('TROPIC-OCH-MIB::tnIroadmPortInfoOSCSfpRxPowerIn')->pluck();

        $portsWithTransceivers = array_unique(array_merge(
            $portsWithTransceivers,
            array_keys($iroadmOscTx),
            array_keys($iroadmOscRx)
        ));

        $transceivers = new Collection;

        foreach ($portsWithTransceivers as $ifIndex) {
            $portId = PortCache::getIdFromIfIndex((int) $ifIndex, $this->getDevice());
            if ($portId === null) {
                continue;
            }

            $ifName = $ifNames[$ifIndex] ?? "Port $ifIndex";

            // Determine type based on port name - OSC ports typically use SFP
            $type = 'SFP';
            if (str_contains(strtoupper((string) $ifName), 'OSCSFP')) {
                $type = 'SFP';  // OSC SFP ports
            }

            $transceivers->push(new Transceiver([
                'port_id' => $portId,
                'index' => (string) $ifIndex,
                'entity_physical_index' => (int) $ifIndex,
                'type' => $type,
                'vendor' => 'Nokia',
            ]));
        }

        return $transceivers;
    }

    /**
     * Discover transceivers for Nokia 1830 PSD devices
     *
     * Uses TROPIC-PSD-MIB::tnPsdSfpInfoTable for SFP module information
     * Falls back to DDM data to infer transceiver presence if SFP info unavailable
     */
    private function discoverPsdTransceivers(): Collection
    {
        // Try tnPsdSfpInfoTable first
        $transceivers = SnmpQuery::enumStrings()->walk([
            'TROPIC-PSD-MIB::tnPsdSfpInfoInvStatus',
            'TROPIC-PSD-MIB::tnPsdSfpInfoPhysicalIdentifier',
            'TROPIC-PSD-MIB::tnPsdSfpInfoVendorName',
            'TROPIC-PSD-MIB::tnPsdSfpInfoPartNumber',
            'TROPIC-PSD-MIB::tnPsdSfpInfoVendorSerialNumber',
            'TROPIC-PSD-MIB::tnPsdSfpInfoWavelength',
            'TROPIC-PSD-MIB::tnPsdSfpInfoVendorDate',
            'TROPIC-PSD-MIB::tnPsdSfpInfoNokiaPartNumber',
            'TROPIC-PSD-MIB::tnPsdSfpInfoConnectorType',
        ])->mapTable(function ($data, $ifIndex) {
            // Check if SFP is present (invStatus should be available(1))
            $invStatus = $data['TROPIC-PSD-MIB::tnPsdSfpInfoInvStatus'] ?? '';
            if ($invStatus !== 'available' && $invStatus !== '1') {
                return null;
            }

            $serial = $this->cleanString($data['TROPIC-PSD-MIB::tnPsdSfpInfoVendorSerialNumber'] ?? null);
            $vendor = $this->cleanString($data['TROPIC-PSD-MIB::tnPsdSfpInfoVendorName'] ?? null);

            // Skip if no identifying info
            if (empty($serial) && empty($vendor)) {
                return null;
            }

            $portId = PortCache::getIdFromIfIndex((int) $ifIndex, $this->getDevice());
            if ($portId === null) {
                return null;
            }

            // Get wavelength (in nm)
            $wavelength = $data['TROPIC-PSD-MIB::tnPsdSfpInfoWavelength'] ?? null;
            if ($wavelength !== null) {
                $wavelength = (int) $wavelength;
                if ($wavelength <= 0) {
                    $wavelength = null;
                }
            }

            // Determine SFP type from physical identifier
            $physId = $data['TROPIC-PSD-MIB::tnPsdSfpInfoPhysicalIdentifier'] ?? '';
            $type = $this->mapPsdTransceiverType($physId);

            // Model from Nokia part number or vendor part number
            $model = $this->cleanString($data['TROPIC-PSD-MIB::tnPsdSfpInfoNokiaPartNumber'] ?? null)
                ?: $this->cleanString($data['TROPIC-PSD-MIB::tnPsdSfpInfoPartNumber'] ?? null);

            // Parse date
            $date = $this->parseDate($data['TROPIC-PSD-MIB::tnPsdSfpInfoVendorDate'] ?? null);

            return new Transceiver([
                'port_id' => $portId,
                'index' => (string) $ifIndex,
                'entity_physical_index' => (int) $ifIndex,
                'type' => $type,
                'vendor' => $vendor,
                'model' => $model,
                'serial' => $serial,
                'date' => $date,
                'wavelength' => $wavelength,
            ]);
        })->filter();

        // If no SFP info available, infer transceivers from DDM data presence
        if ($transceivers->isEmpty()) {
            $transceivers = $this->discoverPsdTransceiversFromDdm();
        }

        return $transceivers;
    }

    /**
     * Discover transceivers from PSD DDM data presence
     *
     * When tnPsdSfpInfoTable is not available, we can infer transceiver presence
     * from tnPsdDdmDataTable - if a port has DDM readings, it has a transceiver
     */
    private function discoverPsdTransceiversFromDdm(): Collection
    {
        $ifNames = SnmpQuery::cache()->walk('IF-MIB::ifName')->pluck();
        $ddmData = SnmpQuery::enumStrings()->walk('TROPIC-PSD-MIB::tnPsdDdmDataValue')->table(2);

        $transceivers = new Collection;

        foreach ($ddmData as $ifIndex => $ddmValues) {
            // If we have any DDM data for this port, it has a transceiver
            if (empty($ddmValues)) {
                continue;
            }

            $portId = PortCache::getIdFromIfIndex((int) $ifIndex, $this->getDevice());
            if ($portId === null) {
                continue;
            }

            $ifName = $ifNames[$ifIndex] ?? "Port $ifIndex";

            // Determine type from port name
            $type = 'SFP';
            if (str_contains(strtoupper((string) $ifName), 'NETWORK')) {
                $type = 'SFP+';  // Network ports typically use SFP+ or higher
            }

            $transceivers->push(new Transceiver([
                'port_id' => $portId,
                'index' => (string) $ifIndex,
                'entity_physical_index' => (int) $ifIndex,
                'type' => $type,
                'vendor' => 'Nokia',
            ]));
        }

        return $transceivers;
    }

    /**
     * Map PSD SFP physical identifier to transceiver type
     */
    private function mapPsdTransceiverType(?string $physId): string
    {
        if (empty($physId)) {
            return 'SFP';
        }

        $physIdLower = strtolower($physId);

        if (str_contains($physIdLower, 'sfp28')) {
            return 'SFP28';
        }
        if (str_contains($physIdLower, 'sfp+') || str_contains($physIdLower, 'sfpplus')) {
            return 'SFP+';
        }
        if (str_contains($physIdLower, 'qsfp28')) {
            return 'QSFP28';
        }
        if (str_contains($physIdLower, 'qsfp')) {
            return 'QSFP';
        }
        if (str_contains($physIdLower, 'xfp')) {
            return 'XFP';
        }

        // Default to SFP for PSD modules
        return 'SFP';
    }

    /**
     * Map module type and acronym code to standardized transceiver type
     *
     * Nokia 1830 uses product codes like:
     * - Q28LR4E = QSFP28 LR4
     * - S10GB-LR = SFP+ 10G LR
     * - C2DCO4 = CFP2 DCO
     * - SEUL1.2O = SFP SE Universal Line
     */
    private function mapTransceiverType(?string $moduleType, ?string $acronymCode): ?string
    {
        // Try to extract type from module type string
        if ($moduleType) {
            $moduleTypeLower = strtolower($moduleType);
            $moduleTypeUpper = strtoupper($moduleType);

            // Nokia-specific product code patterns
            // QSFP28 codes start with Q28
            if (str_starts_with($moduleTypeUpper, 'Q28') || str_starts_with($moduleTypeUpper, 'QSFP28')) {
                return 'QSFP28';
            }
            // QSFP-DD codes
            if (str_starts_with($moduleTypeUpper, 'QDD') || str_contains($moduleTypeLower, 'qsfp-dd') || str_contains($moduleTypeLower, 'qsfpdd')) {
                return 'QSFP-DD';
            }
            // QSFP+ codes (Q+ prefix or QP prefix for 40G)
            if (preg_match('/^Q\+|^QP|^Q40/i', $moduleType) || str_contains($moduleTypeLower, 'qsfp+') || str_contains($moduleTypeLower, 'qsfpplus')) {
                return 'QSFP+';
            }
            // Generic QSFP
            if (str_starts_with($moduleTypeUpper, 'Q') && preg_match('/^Q\d/i', $moduleType)) {
                return 'QSFP';
            }

            // CFP2 codes start with C2
            if (str_starts_with($moduleTypeUpper, 'C2') || str_contains($moduleTypeLower, 'cfp2')) {
                return 'CFP2';
            }
            // CFP4 codes
            if (str_starts_with($moduleTypeUpper, 'C4') || str_contains($moduleTypeLower, 'cfp4')) {
                return 'CFP4';
            }
            // CFP8 codes
            if (str_starts_with($moduleTypeUpper, 'C8') || str_contains($moduleTypeLower, 'cfp8')) {
                return 'CFP8';
            }
            // Generic CFP
            if (str_contains($moduleTypeLower, 'cfp')) {
                return 'CFP';
            }

            // XFP codes
            if (str_starts_with($moduleTypeUpper, 'XFP') || str_contains($moduleTypeLower, 'xfp')) {
                return 'XFP';
            }

            // SFP28 codes
            if (str_starts_with($moduleTypeUpper, 'S28') || str_contains($moduleTypeLower, 'sfp28')) {
                return 'SFP28';
            }
            // SFP+ codes (S10GB, S10G prefix for 10G, or SFP+)
            if (preg_match('/^S10G|^S10-|^S10\+/i', $moduleType) || str_contains($moduleTypeLower, 'sfp+') || str_contains($moduleTypeLower, 'sfpplus')) {
                return 'SFP+';
            }
            // SFP-DD codes
            if (str_contains($moduleTypeLower, 'sfp-dd') || str_contains($moduleTypeLower, 'sfpdd')) {
                return 'SFP-DD';
            }
            // Generic SFP codes (S prefix with numbers, or SFP, or SE Universal Line)
            if (preg_match('/^S[0-9]|^S[EG]|^SFP/i', $moduleType) || str_contains($moduleTypeLower, 'sfp')) {
                return 'SFP';
            }
        }

        // Try acronym code if module type didn't give us a result
        if ($acronymCode) {
            $acronymLower = strtolower($acronymCode);

            if (str_contains($acronymLower, 'qsfp28')) {
                return 'QSFP28';
            }
            if (str_contains($acronymLower, 'qsfp')) {
                return 'QSFP';
            }
            if (str_contains($acronymLower, 'sfp28')) {
                return 'SFP28';
            }
            if (str_contains($acronymLower, 'sfp+')) {
                return 'SFP+';
            }
            if (str_contains($acronymLower, 'sfp')) {
                return 'SFP';
            }
            if (str_contains($acronymLower, 'xfp')) {
                return 'XFP';
            }
            if (str_contains($acronymLower, 'cfp2')) {
                return 'CFP2';
            }
            if (str_contains($acronymLower, 'cfp')) {
                return 'CFP';
            }
        }

        // Return module type as-is if no match found
        return $moduleType ?: $acronymCode;
    }

    /**
     * Parse date string from tnSfpPortDate
     */
    private function parseDate(?string $dateStr): ?string
    {
        if (empty($dateStr)) {
            return null;
        }

        // Try to parse various date formats
        // Common formats: YYMMDD, YYYY-MM-DD, etc.
        $dateStr = trim($dateStr);

        // Try YYMMDD format (6 digits)
        if (preg_match('/^(\d{2})(\d{2})(\d{2})$/', $dateStr, $matches)) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
            $day = (int) $matches[3];

            // Assume 20xx for years 00-99
            $year += 2000;

            if ($month >= 1 && $month <= 12 && $day >= 1 && $day <= 31) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        // Try YYYYMMDD format (8 digits)
        if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $dateStr, $matches)) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
            $day = (int) $matches[3];

            if ($year >= 1970 && $year <= 2100 && $month >= 1 && $month <= 12 && $day >= 1 && $day <= 31) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        // Try ISO format YYYY-MM-DD
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $dateStr, $matches)) {
            return $matches[0];
        }

        return null;
    }

    /**
     * Clean and trim SNMP string values
     */
    private function cleanString(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }
}
