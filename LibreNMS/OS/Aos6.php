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

        // Get entity information from Entity-MIB
        $entClass = \SnmpQuery::walk('ENTITY-MIB::entPhysicalClass')->pluck();
        $entDescr = \SnmpQuery::walk('ENTITY-MIB::entPhysicalDescr')->pluck();
        $entVendor = \SnmpQuery::walk('ENTITY-MIB::entPhysicalMfgName')->pluck();
        $entModel = \SnmpQuery::walk('ENTITY-MIB::entPhysicalModelName')->pluck();
        $entSerial = \SnmpQuery::walk('ENTITY-MIB::entPhysicalSerialNum')->pluck();

        // Get all ports ordered by ifIndex for positional mapping
        $ports = \DB::table('ports')
            ->where('device_id', $this->getDevice()->device_id)
            ->orderBy('ifIndex')
            ->get();

        $transceivers = collect();
        $portIndex = 0;

        foreach ($mfgDates as $entIndex => $mfgDate) {
            $class = $entClass[$entIndex] ?? null;

            // Only process transceivers (class 10)
            if ($class != 10) {
                continue;
            }

            // Map transceivers to ports by position (in sorted order)
            if (! isset($ports[$portIndex])) {
                continue;
            }

            $port = $ports[$portIndex];
            $portId = (int) $port->port_id;
            $portIfIndex = (int) $port->ifIndex;
            $portIndex++;

            $transceivers->push(new Transceiver([
                'port_id' => $portId,
                'index' => $entIndex,
                'entity_physical_index' => $portIfIndex,  // Use ifIndex for DDM sensor matching
                'type' => $entDescr[$entIndex] ?? null,
                'vendor' => $entVendor[$entIndex] ?? null,
                'model' => $entModel[$entIndex] ?? null,
                'serial' => $entSerial[$entIndex] ?? null,
                'date' => $mfgDate,
            ]));
        }

        // Update sensor group to 'transceiver' for DDM sensors matching these ports
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