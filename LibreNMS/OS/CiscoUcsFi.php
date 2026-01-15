<?php

namespace LibreNMS\OS;

use Illuminate\Support\Collection;
use LibreNMS\OS\Shared\Cisco as BaseCisco;

/**
 * Cisco UCS Fabric Interconnect OS
 *
 * Discovery priorities:
 *  - Interfaces: IF-MIB/ifXTable (handled by core)
 *  - Hardware/serial: ENTITY-MIB
 *  - Sensors: ENTITY-SENSOR-MIB (handled by core + Cisco includes)
 *  - VLANs: handled by core Modules\Vlans (Q-BRIDGE first, Cisco fallbacks)
 */
class CiscoUcsFi extends BaseCisco
{
    /**
     * Discover OS specifics for UCS FI.
     * Avoid hard-coding entPhysicalIndex; pick the root chassis from ENTITY-MIB.
     */
    public function discoverOS(\App\Models\Device $device): void
    {
        // Let parent Cisco discovery try first (serial, version, etc.)
        parent::discoverOS($device);

       $hardwareReady = ! empty($device->hardware) && stripos((string) $device->hardware, 'ciscoModules') === false;

        // 1) UCS MIB product name/serial (preferred): cucsNetworkElement productName (col 11), serial (col 17)
        try {
            // Walk individual column OIDs as table() doesn't parse numeric walks correctly for this MIB
            $productNames = \SnmpQuery::numeric()->walk('.1.3.6.1.4.1.9.9.719.1.32.1.1.11')->values();
            $serials = \SnmpQuery::numeric()->walk('.1.3.6.1.4.1.9.9.719.1.32.1.1.17')->values();

            // Try product name first
            if (! empty($productNames)) {
                $productName = reset($productNames);  // Get first value
                if (is_string($productName) && $productName !== '') {
                    $device->hardware = $this->sanitizeString($productName);   // e.g., UCS-FI-6332-16UP
                    $hardwareReady = true;
                }
            }

            // Try serial number
            if (! empty($serials)) {
                $serial = reset($serials);  // Get first value
                if (is_string($serial) && $serial !== '') {
                    $device->serial = $this->sanitizeString($serial);
                }
            }
        } catch (\Throwable $e) {
            \Log::debug('UCS FI: model/serial (cucsNetworkElement) read failed: ' . $e->getMessage());
        }

        if ($hardwareReady) {
            return;
        }

        // 2) ENTITY-MIB fallback: root chassis (no hard-coded entPhysicalIndex)
        $entity = \SnmpQuery::walk('ENTITY-MIB::entPhysicalTable')->table();
        if (! empty($entity)) {
            $rootIdx = null;
            foreach ($entity as $idx => $row) {
                $contained = $row['entPhysicalContainedIn'] ?? null;
                $class = strtolower($row['entPhysicalClass'] ?? '');
                if ($contained === '0' && ($class === 'chassis' || $class === 'stack' || $class === 'module' || $class === 'unknown')) {
                    $rootIdx = $idx;
                    break;
                }
            }
            if ($rootIdx === null) {
                foreach ($entity as $idx => $row) {
                    if (($row['entPhysicalContainedIn'] ?? null) === '0') {
                        $rootIdx = $idx;
                        break;
                    }
                }
            }
            if ($rootIdx !== null) {
                $m = $entity[$rootIdx]['entPhysicalModelName'] ?? '';
                if (! $m) {
                    $m = $entity[$rootIdx]['entPhysicalName'] ?? '';
                }
                if (! $m) {
                    $m = $entity[$rootIdx]['entPhysicalDescr'] ?? '';
                }
                if ($m) {
                    $device->hardware = $this->sanitizeString($m);
                }
            }
        }

        // 3) sysDescr fallback for UCS-FI pattern
        if (empty($device->hardware) || stripos((string) $device->hardware, 'ciscoModules') !== false) {
            $sysDescr = \SnmpQuery::get('SNMPv2-MIB::sysDescr.0')->value();
            if (preg_match('/(UCS-FI-[0-9A-Za-z\-]+)/', (string) $sysDescr, $m)) {
                $device->hardware = $this->sanitizeString($m[1]);
            }
        }
    }

    /**
     * Keep UI-safe strings: ensure UTF-8, strip any 4-byte codepoints (if DB/renderer can't handle them),
     * and drop invalid sequences.
     */
    private function sanitizeString(?string $s): ?string
    {
        if ($s === null || $s === '') {
            return $s;
        }

        // Ensure valid UTF-8
        if (! mb_check_encoding($s, 'UTF-8')) {
            $s = mb_convert_encoding($s, 'UTF-8', 'auto');
        }

        // Strip 4-byte UTF-8 sequences (U+10000..U+10FFFF) to match non-utf8mb4 DBs safely
        $s = preg_replace('/[\xF0-\xF7][\x80-\xBF]{3}/', '', $s);

        // Drop any remaining invalid sequences
        $s = @iconv('UTF-8', 'UTF-8//IGNORE', (string) $s);

        return $s;
    }

    /**
     * Discover transceivers on UCS FI.
     * UCS FI may use different ENTITY-MIB structures or vendor types than standard Cisco devices.
     */
    public function discoverTransceivers(): Collection
    {
        // Try parent Cisco discovery first
        $transceivers = parent::discoverTransceivers();

        if ($transceivers->isNotEmpty()) {
            return $transceivers;
        }

        // UCS FI specific: Look for port entities that may contain transceivers
        // UCS FI might report transceivers with different vendor types or as port entities
        $additionalContainers = [
            'cevPortTenGigBaseEthernet',
            'cevPortFortyGigBaseEthernet',
            'cevPortHundredGigBaseEthernet',
            'cevModuleUcs6100Fabric',
            'cevModuleUcsFabricInterconnect',
            'cevContainerUCSFI',
        ];

        // Get entity physical data
        $snmpData = \SnmpQuery::cache()->hideMib()->mibs(['CISCO-ENTITY-VENDORTYPE-OID-MIB'])->walk('ENTITY-MIB::entPhysicalTable')->table(1);
        if (empty($snmpData)) {
            return new Collection;
        }

        $snmpData = collect(\SnmpQuery::hideMib()->mibs(['IF-MIB'])->walk('ENTITY-MIB::entAliasMappingIdentifier')->table(1, $snmpData));

        // Look for entities with class 'port' that might be transceiver cages/ports
        $portEntities = $snmpData->filter(function ($ent) use ($additionalContainers) {
            $class = strtolower($ent['entPhysicalClass'] ?? '');
            $vendorType = $ent['entPhysicalVendorType'] ?? '';

            // Check if it's a port class or has a UCS-specific vendor type
            return $class === 'port' || in_array($vendorType, $additionalContainers);
        });

        if ($portEntities->isEmpty()) {
            return new Collection;
        }

        // For each port entity, look for child modules/transceivers
        $transceiverData = $snmpData->filter(function ($ent) use ($portEntities) {
            $containedIn = $ent['entPhysicalContainedIn'] ?? null;
            $class = strtolower($ent['entPhysicalClass'] ?? '');

            // Look for module class entities contained within port entities
            return $class === 'module' && $portEntities->has($containedIn);
        });

        // Also check direct port entities if they look like transceivers
        $directPorts = $portEntities->filter(function ($ent) {
            $descr = strtolower($ent['entPhysicalDescr'] ?? '');

            // Check if description suggests it's a transceiver
            return str_contains($descr, 'sfp') ||
                   str_contains($descr, 'xfp') ||
                   str_contains($descr, 'qsfp') ||
                   str_contains($descr, 'gbic') ||
                   str_contains($descr, 'transceiver');
        });

        $allTransceivers = $transceiverData->merge($directPorts);

        return $allTransceivers->map(function ($ent, $index) use ($portEntities) {
            $ent['entPhysicalIndex'] = $index;

            // Determine ifIndex
            $ifIndex = null;
            if (isset($ent['entAliasMappingIdentifier'][0])) {
                $ifIndex = preg_replace('/^.*ifIndex[.[](\d+).*$/', '$1', (string) $ent['entAliasMappingIdentifier'][0]);
            } else {
                // Try parent port's ifIndex
                $parentIdx = $ent['entPhysicalContainedIn'] ?? null;
                if ($parentIdx && isset($portEntities[$parentIdx]['entAliasMappingIdentifier'][0])) {
                    $ifIndex = preg_replace('/^.*ifIndex[.[](\d+).*$/', '$1', (string) $portEntities[$parentIdx]['entAliasMappingIdentifier'][0]);
                }
            }

            return new \App\Models\Transceiver([
                'port_id' => (int) \App\Facades\PortCache::getIdFromIfIndex($ifIndex, $this->getDevice()),
                'index' => $index,
                'type' => $ent['entPhysicalDescr'] ?? null,
                'vendor' => $ent['entPhysicalMfgName'] ?? null,
                'revision' => $ent['entPhysicalHardwareRev'] ?? null,
                'model' => $ent['entPhysicalModelName'] ?? null,
                'serial' => $ent['entPhysicalSerialNum'] ?? null,
                'entity_physical_index' => $index,
            ]);
        })->filter(fn ($trans) =>
            // Only include if we found a valid port_id
            $trans->port_id > 0);
    }

    /**
     * Defer VLAN discovery to the core (Q-BRIDGE first, then Cisco fallbacks).
     * Apply UCS FI specific normalizations (byte-swap correction, UCSM hint).
     */
    public function discoverVlans(): Collection
    {
        $vlans = parent::discoverVlans();

        // UCS Manager mode hint: only VLAN 1 exposed (and sometimes byte-swapped index)
        if ($vlans->count() === 1 && ($vlans->first()->vlan_vlan ?? 0) >= 16777216) {
            \Log::warning('UCS Fabric Interconnect appears to expose only VLAN 1 (likely UCSM mode)');
        }

        // Fix byte-swapped VLAN IDs seen on some FI firmwares (e.g., 0x01000000 should be 1)
        return $vlans->map(function ($vlan) {
            if (isset($vlan->vlan_vlan) && $vlan->vlan_vlan >= 16777216) {
                $actualVlanId = ($vlan->vlan_vlan >> 24) & 0xFF;
                \Log::debug("UCS FI: Correcting byte-swapped VLAN ID {$vlan->vlan_vlan} to {$actualVlanId}");
                $vlan->vlan_vlan = $actualVlanId;
            }

            return $vlan;
        });
    }
}
