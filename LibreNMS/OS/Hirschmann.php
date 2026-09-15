<?php

namespace LibreNMS\OS;

use App\Models\Device;
use App\Models\PortVlan;
use App\Models\Vlan;
use App\Facades\PortCache;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\VlanDiscovery;
use LibreNMS\Interfaces\Discovery\VlanPortDiscovery;
use LibreNMS\OS;
use LibreNMS\Util\StringHelpers;
use SnmpQuery;

class Hirschmann extends OS implements VlanDiscovery, VlanPortDiscovery
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device);

        $version = $this->normalizeHiosVersion($device->version);
        if ($version) {
            $device->version = $version;

            return;
        }

        $version_data = SnmpQuery::cache()->get([
            'HM2-DEVMGMT-MIB::hm2DevMgmtSwVersion.1.1.1',
            'HM2-DEVMGMT-MIB::hm2DevMgmtSwVersion.2.1.1',
            'HM2-DEVMGMT-MIB::hm2DevMgmtSwVersion.2.1.2',
            'HM2-DEVMGMT-MIB::hm2DevMgmtSwVersBootcode.0',
        ])->values();

        foreach ($version_data as $candidate) {
            $version = $this->normalizeHiosVersion($candidate);
            if ($version) {
                $device->version = $version;

                return;
            }
        }
    }

    public function discoverVlans(): Collection
    {
        $vlans = parent::discoverVlans();

        $vlan_tables = [
            'HM2-PLATFORM-SWITCHING-MIB::hm2AgentPrivateVlanTable',
            'HM2-PLATFORM-SWITCHING-MIB::hm2AgentSwitchSnoopingVlanTable',
            'HM2-PLATFORM-SWITCHING-MIB::hm2AgentDaiVlanConfigTable',
            'HM2-PLATFORM-SWITCHING-MIB::hm2AgentDaiVlanStatsTable',
            'HM2-PLATFORM-SWITCHING-MIB::hm2AgentDhcpSnoopingVlanConfigTable',
            'HM2-PLATFORM-SWITCHING-MIB::hm2AgentDhcpL2RelayVlanConfigTable',
            'HM2-PLATFORM-SWITCHING-MIB::hm2AgentSwitchSnoopingQuerierVlanTable',
        ];

        $vlan_names = $vlans
            ->where('vlan_domain', 1)
            ->mapWithKeys(fn (Vlan $vlan) => [$vlan->vlan_vlan => $vlan->vlan_name])
            ->all();
        $seen = array_fill_keys($vlans->where('vlan_domain', 1)->pluck('vlan_vlan')->all(), true);

        foreach ($vlan_tables as $table_oid) {
            $table = SnmpQuery::cache()->walk($table_oid)->valuesByIndex();

            foreach ($table as $index => $_data) {
                $vlan_id = $this->vlanIdFromIndex($index);

                if ($vlan_id === null || isset($seen[$vlan_id])) {
                    continue;
                }

                $seen[$vlan_id] = true;

                $vlans->push(new Vlan([
                    'vlan_vlan' => $vlan_id,
                    'vlan_domain' => 1,
                    'vlan_name' => $vlan_names[$vlan_id] ?? 'VLAN ' . $vlan_id,
                ]));
            }
        }

        return $vlans->sortBy('vlan_vlan')->values();
    }

    public function discoverVlanPorts(Collection $vlans): Collection
    {
        $ports = parent::discoverVlanPorts($vlans);

        if ($ports->isNotEmpty()) {
            return $ports;
        }

        $snooping_intf_table = SnmpQuery::cache()->walk('HM2-PLATFORM-SWITCHING-MIB::hm2AgentSwitchSnoopingIntfTable')->valuesByIndex();

        foreach ($snooping_intf_table as $index => $data) {
            $ifIndex = (int) strtok($index, '.');
            $baseport = $this->bridgePortFromIfIndex($ifIndex);

            if (! $baseport) {
                continue;
            }

            $vlan_ids = StringHelpers::bitsToIndices((string) ($data['HM2-PLATFORM-SWITCHING-MIB::hm2AgentSwitchSnoopingIntfVlanIDs'] ?? ''));

            foreach ($vlan_ids as $vlan_id) {
                $ports->push(new PortVlan([
                    'vlan' => $vlan_id,
                    'baseport' => $baseport,
                    'untagged' => 0,
                    'port_id' => PortCache::getIdFromIfIndex($ifIndex, $this->getDeviceId()) ?? 0,
                ]));
            }
        }

        return $ports;
    }

    private function vlanIdFromIndex(string $index): ?int
    {
        $first_segment = explode('.', $index, 2)[0];

        return is_numeric($first_segment) ? (int) $first_segment : null;
    }

    private function normalizeHiosVersion(mixed $version): ?string
    {
        if (! is_string($version) || $version === '') {
            return null;
        }

        if (preg_match('/^HiOS(?:-[A-Za-z0-9]+)?[-\s]+(?<version>\d+(?:\.\d+)+)/', $version, $matches)) {
            return 'HiOS ' . $matches['version'];
        }

        if (preg_match('/^HiBOOT-[^-]+-(?<version>\d+(?:\.\d+)+)/', $version, $matches)) {
            return 'HiOS ' . $matches['version'];
        }

        return null;
    }
}
