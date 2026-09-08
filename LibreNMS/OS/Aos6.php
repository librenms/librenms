<?php

/**
 * Aos6.php
 *
 * -Description-
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
 * @copyright  2025 Peca Nesovanovic
 * @copyright  2025 Tony Murray
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\EntPhysical;
use App\Models\PortVlan;
use App\Models\Transceiver;
use App\Models\Vlan;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\Interfaces\Discovery\VlanDiscovery;
use LibreNMS\Interfaces\Discovery\VlanPortDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Aos6 extends OS implements TransceiverDiscovery, VlanDiscovery, VlanPortDiscovery
{
    public function discoverTransceivers(): Collection
    {
        $mfgDates = [];

        // Walk manufacturing date OID - supports both text and numeric formats
        $dates = \SnmpQuery::walk('ALCATEL-IND1-CHASSIS-MIB::chasEntPhysMfgDate')->pluck();

        foreach ($dates as $entIndex => $dateStr) {
            $dateStr = trim((string) $dateStr);

            if (empty($dateStr)) {
                continue;
            }

            // Try text format "AUG 15 2015" first
            $parsed = \DateTime::createFromFormat('M d Y', $dateStr);

            // If that fails, try numeric YYMMDD format "151012"
            if ($parsed === false) {
                $parsed = \DateTime::createFromFormat('ymd', $dateStr);
            }

            if ($parsed === false) {
                continue;
            }

            $mfgDates[$entIndex] = $parsed->format('Y-m-d');
        }

        // Get entity information from database (already discovered by entity-physical module)
        $entities = EntPhysical::where('device_id', $this->getDeviceId())
            ->where('entPhysicalClass', 'port') // class 10 = port
            ->get()
            ->keyBy('entPhysicalIndex');

        // Get port information for matching via ifDescr format
        $ifDescrs = \SnmpQuery::walk('IF-MIB::ifDescr')->pluck();

        // Build entity index to ifIndex mapping by matching entPhysicalParentRelPos with ifDescr port number
        $entIndexToIfIndex = [];

        foreach ($entities as $entIndex => $entity) {
            $relPos = $entity->entPhysicalParentRelPos;

            if ($relPos < 0) {
                continue;
            }

            // Try to find matching ifIndex by port description
            // For AOS6, ifDescr format is "Alcatel-Lucent Enterprise 1/25"
            foreach ($ifDescrs as $ifIndex => $ifDescr) {
                // Match patterns like "1/25" in ifDescr where 25 is the relPos
                if (preg_match('/1\/(\d+)$/', trim((string) $ifDescr), $matches)) {
                    if ((int) $matches[1] == $relPos) {
                        $entIndexToIfIndex[$entIndex] = (int) $ifIndex;
                        break;
                    }
                }
            }
        }

        $transceivers = collect();

        foreach ($mfgDates as $entIndex => $mfgDate) {
            $entity = $entities[$entIndex] ?? null;

            if (! $entity) {
                continue;
            }

            // Get ifIndex for this entity using the mapping
            $ifIndex = $entIndexToIfIndex[$entIndex] ?? null;
            if (! $ifIndex) {
                continue;
            }

            // Get port_id from ifIndex
            $portId = PortCache::getIdFromIfIndex($ifIndex, $this->getDeviceId());
            if (! $portId) {
                continue;
            }

            $transceivers->push(new Transceiver([
                'port_id' => $portId,
                'index' => $entIndex,
                'entity_physical_index' => $ifIndex,  // ifIndex matches entPhysicalIndex in sensors from entity-sensor.inc.php
                'type' => $entity->entPhysicalDescr ?? null,
                'vendor' => $entity->entPhysicalMfgName ?? null,
                'model' => $entity->entPhysicalModelName ?? null,
                'serial' => $entity->entPhysicalSerialNum ?? null,
                'date' => $mfgDate,
            ]));
        }

        // Update sensor group='transceiver' for DDM sensors to enable Health > Transceivers tab grouping
        // Required because entity-sensor.inc.php doesn't set group for AOS6 (unlike comware/cisco which
        // use OS-specific sensor discovery files or custom entity-sensor logic to set group at creation)
        if ($transceivers->isNotEmpty()) {
            $ifIndexes = $transceivers->pluck('entity_physical_index')->toArray();

            \DB::table('sensors')
                ->where('device_id', $this->getDevice()->device_id)
                ->whereIn('entPhysicalIndex', $ifIndexes)
                ->whereIn('sensor_class', ['dbm', 'voltage', 'current', 'temperature'])
                ->update(['group' => 'transceiver']);
        }

        return $transceivers;
    }

    public function discoverVlans(): Collection
    {
        if (($QBridgeMibVlans = parent::discoverVlans())->isNotEmpty()) {
            return $QBridgeMibVlans;
        }

        return SnmpQuery::walk('ALCATEL-IND1-VLAN-MGR-MIB::vlanDescription')
            ->mapTable(fn ($vlans, $vlan_id) => new Vlan([
                'vlan_vlan' => $vlan_id,
                'vlan_name' => $vlans['ALCATEL-IND1-VLAN-MGR-MIB::vlanDescription'] ?? null,
                'vlan_domain' => 1,
                'vlan_type' => null,
            ]));
    }

    public function discoverVlanPorts(Collection $vlans): Collection
    {
        if (($QBridgeMibPorts = parent::discoverVlanPorts($vlans))->isNotEmpty()) {
            return $QBridgeMibPorts;
        }

        return SnmpQuery::walk('ALCATEL-IND1-VLAN-MGR-MIB::vpaType')
            ->mapTable(fn ($data, $vpaVlanNumber, $vpaIfIndex = null) => new Portvlan([
                'vlan' => $vpaVlanNumber,
                'baseport' => $this->bridgePortFromIfIndex($vpaIfIndex),
                'untagged' => ($data['ALCATEL-IND1-VLAN-MGR-MIB::vpaType'] == 1 ? 1 : 0),
                'port_id' => PortCache::getIdFromIfIndex($vpaIfIndex, $this->getDeviceId()) ?? 0, // ifIndex from device
            ]));
    }
}
