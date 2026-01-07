<?php
/**
 * CiscoUcsFi.php
 *
 * Cisco UCS Fabric Interconnect
 * Uses standard LibreNMS discovery for:
 * - Interfaces (IF-MIB, ifXTable)
 * - Hardware inventory (ENTITY-MIB)
 * - Sensors (ENTITY-SENSOR-MIB)
 * - VLANs (CISCO-VTP-MIB via custom discovery module)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026
 * @author     LibreNMS Contributors
 */

namespace LibreNMS\OS;

use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\VlanDiscovery;
use LibreNMS\OS\Shared\Cisco;

class CiscoUcsFi extends Cisco implements OSDiscovery, VlanDiscovery
{
    /**
     * Map sysObjectID to UCS Fabric Interconnect model names
     */
    private const MODEL_MAP = [
        '.1.3.6.1.4.1.9.12.3.1.3.1062' => 'Cisco UCS 6248UP 48-Port Fabric Interconnect',
        '.1.3.6.1.4.1.9.12.3.1.3.1063' => 'Cisco UCS 6296UP 96-Port Fabric Interconnect',
        '.1.3.6.1.4.1.9.12.3.1.3.1488' => 'Cisco UCS 6332 32-Port Fabric Interconnect',
        '.1.3.6.1.4.1.9.12.3.1.3.1662' => 'Cisco UCS 6332-16UP Fabric Interconnect',
    ];

    /**
     * Discover OS-specific information
     * UCS FI runs NX-OS but is not a typical NX-OS device
     * Avoid Cisco legacy subtrees and UCSM-specific MIBs
     */
    public function discoverOS(\App\Models\Device $device): void
    {
        // Use parent (Cisco) discovery for version/hardware/serial
        parent::discoverOS($device);

        // Get hardware model name from ENTITY-MIB entPhysicalModelName
        // UCS FI chassis typically has entPhysicalIndex 10
        $modelName = snmp_get($this->getDeviceArray(), 'ENTITY-MIB::entPhysicalModelName.10', '-Oqv');

        if (!empty($modelName) && $modelName !== '""') {
            // Use the actual model name from ENTITY-MIB (e.g., "UCS-FI-6332-16UP")
            $device->hardware = trim($modelName, '"');
        } elseif (empty($device->hardware)) {
            // Fallback to MODEL_MAP if ENTITY-MIB doesn't provide model name
            if (isset(self::MODEL_MAP[$device->sysObjectID])) {
                $device->hardware = self::MODEL_MAP[$device->sysObjectID];
            }
        }

        // UCS FI specific notes:
        // - Uses ENTITY-MIB for hardware inventory (handled by entity-physical module)
        // - Uses ENTITY-SENSOR-MIB for sensors (handled by entity-sensor module)
        // - Uses CISCO-VTP-MIB for VLAN discovery (inherited from Cisco parent)
        // - Uses IF-MIB/ifXTable for interface stats (standard LibreNMS)
        // - May use CISCO-FLASH-MIB for filesystem stats (optional)
    }

    /**
     * Discover VLANs using CISCO-VTP-MIB
     * Some UCS FI firmware versions (particularly 6332-16UP) have a bug where VLAN IDs
     * are byte-swapped in the SNMP index (e.g., 16777216 instead of 1)
     *
     * Note: VLAN-to-port membership is NOT reliably available via SNMP on UCS FI
     * We only discover VLAN inventory, not port assignments
     *
     * @return Collection<\App\Models\Vlan>
     */
    public function discoverVlans(): Collection
    {
        $vlans = parent::discoverVlans();

        // Check if in UCS Manager mode (only VLAN 1 with byte-swapped index indicates UCSM mode)
        if ($vlans->count() == 1 && $vlans->first()->vlan_vlan >= 16777216) {
            \Log::warning("UCS Fabric Interconnect appears to be in UCS Manager mode. VLANs are managed by UCS Manager and not exposed via SNMP on the fabric interconnect. Only VLAN 1 will be discovered. To discover all VLANs, monitor the UCS Manager instead or use SSH-based discovery.");
        }

        // Fix byte-swapped VLAN IDs (firmware bug on some UCS FI models)
        // 16777216 (0x01000000) should be 1, etc.
        return $vlans->map(function ($vlan) {
            if ($vlan->vlan_vlan >= 16777216) {
                // Extract actual VLAN ID from byte-swapped value (big-endian to little-endian)
                $actualVlanId = ($vlan->vlan_vlan >> 24) & 0xFF;
                \Log::debug("UCS FI: Correcting byte-swapped VLAN ID {$vlan->vlan_vlan} to {$actualVlanId}");
                $vlan->vlan_vlan = $actualVlanId;
            }
            return $vlan;
        });
    }
}
