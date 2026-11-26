<?php

/*
 * Cumulus.php
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\BgpPeer;
use App\Models\Device;
use App\Models\Sensor;
use App\Models\Vrf;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use LibreNMS\OS\Traits\NetSnmpSensors;
use LibreNMS\Util\Bgp;
use LibreNMS\Util\IP;
use SnmpQuery;

class Cumulus extends \LibreNMS\OS
{
    use NetSnmpSensors;

    /** BgpPeer column to the column of both CUMULUS-BGPVRF-MIB and CUMULUS-BGPUN-MIB */
    private const array BGP_COLUMNS = [
        'bgpPeerRemoteAs' => 'bgpPeerRemoteAs',
        'bgpPeerState' => 'bgpPeerState',
        'bgpPeerAdminStatus' => 'bgpPeerAdminStatus',
        'bgpLocalAddr' => 'bgpPeerLocalAddr',
        'bgpPeerDescr' => 'bgpPeerDesc',
        'bgpPeerLastError' => 'bgpPeerLastError',
        'bgpPeerFsmEstablishedTime' => 'bgpPeerFsmEstablishedTime',
        'bgpPeerInUpdateElapsedTime' => 'bgpPeerInUpdateElapsedTime',
        'bgpPeerInUpdates' => 'bgpPeerInUpdates',
        'bgpPeerOutUpdates' => 'bgpPeerOutUpdates',
        'bgpPeerInTotalMessages' => 'bgpPeerInTotalMessages',
        'bgpPeerOutTotalMessages' => 'bgpPeerOutTotalMessages',
    ];

    public function discoverOS(Device $device): void
    {
        $response = \SnmpQuery::next([
            'ENTITY-MIB::entPhysicalDescr',
            'ENTITY-MIB::entPhysicalSoftwareRev',
            'ENTITY-MIB::entPhysicalSerialNum',
        ]);

        $device->hardware = $response->value('ENTITY-MIB::entPhysicalDescr') ?: null;
        $device->serial = $response->value('ENTITY-MIB::entPhysicalSerialNum') ?: null;
        $device->version = preg_replace('/^Cumulus Linux /', '', (string) $response->value('ENTITY-MIB::entPhysicalSoftwareRev')) ?: null;
    }

    /**
     * CUMULUS-BGPVRF-MIB lists the VRFs by their id
     */
    public function discoverVrfs(): Collection
    {
        return SnmpQuery::hideMib()->walk('CUMULUS-BGPVRF-MIB::bgpVrfName')->throwIfConnectionError()->mapTable(fn ($data, $vrfId) => new Vrf([
            'vrf_oid' => (string) $vrfId,
            'vrf_name' => $data['bgpVrfName'] ?? '',
            'mplsVpnVrfDescription' => '',
        ]))->filter(fn (Vrf $vrf) => $vrf->vrf_name !== '')->values();
    }

    public function discoverVrfPorts(Collection $vrfs): Collection
    {
        return new Collection;
    }

    /**
     * CUMULUS-BGPVRF-MIB lists the sessions of every VRF, indexed by VRF id.
     * Older releases only have CUMULUS-BGPUN-MIB, which adds unnumbered sessions to BGP4-MIB.
     *
     * @return Collection<int, BgpPeer>
     */
    public function discoverBgpPeers(): Collection
    {
        $peers = collect($this->walkBgpTable('', 'CUMULUS-BGPVRF-MIB', [
            ...self::BGP_COLUMNS,
            'address' => 'bgpPeerRemoteAddr',
            'iface' => 'bgpPeerIfindex',
        ]))->map(function (array $row, int|string $index): ?BgpPeer {
            $address = IP::fromHexString($row['address'] ?? '', true);
            $vrfId = $this->bgpVrfId(explode('.', (string) $index)[0]);

            return $this->makeBgpPeer($address, $row['iface'] ?? null, '', $vrfId, Arr::except($row, ['address', 'iface']));
        })->filter()->values();

        if ($peers->isNotEmpty()) {
            return $peers;
        }

        $peers = collect($this->walkBgpTable('', 'CUMULUS-BGPUN-MIB', [...self::BGP_COLUMNS, 'iface' => 'bgpPeerIface']))->map(function (array $row, int|string $index): ?BgpPeer {
            $parts = explode('.', (string) $index);

            return $this->makeBgpPeer(Bgp::shiftIp($parts), $row['iface'] ?? null, '', null, Arr::except($row, ['iface']));
        })->filter()->values();

        return $peers->isNotEmpty() ? $peers : $this->discoverBgp4MibPeers('');
    }

    public function discoverBgpLocalAs(): ?int
    {
        return $this->bgpLocalAsFallback(parent::discoverBgpLocalAs(), 'CUMULUS-BGPVRF-MIB::bgpLocalAs');
    }

    protected function discoverOsSensors(): Collection
    {
        $sensors = parent::discoverOsSensors()->merge($this->discoverNetSnmpSensors());

        // hardware states are the oper status of the ENTITY-SENSOR-MIB sensors, the index tells the kind
        foreach (SnmpQuery::cache()->walk('ENTITY-SENSOR-MIB::entPhySensorOperStatus')->pluck() as $index => $status) {
            if (preg_match('/^1100000\d{2}$/', (string) $index)) {
                $kind = 'Power Supply';
            } elseif (preg_match('/^1000110\d{2}$/', (string) $index)) {
                $kind = 'Fan';
            } elseif (preg_match('/^1000000\d{2}$/', (string) $index)) {
                $kind = 'Temperature Sensor';
            } else {
                continue;
            }

            $sensors->push(new Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'state',
                'sensor_oid' => ".1.3.6.1.2.1.99.1.1.1.5.$index",
                'sensor_index' => $index,
                'sensor_type' => 'cumulus_hardware_state',
                'sensor_descr' => "$kind " . substr((string) $index, -1) . ' State',
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_current' => $status,
                'entPhysicalIndex' => $index,
                'rrd_type' => 'GAUGE',
            ])->withStates([
                ['value' => 1, 'generic' => 0, 'descr' => 'OK'],
                ['value' => 2, 'generic' => 3, 'descr' => 'UNAVAILABLE'],
                ['value' => 3, 'generic' => 2, 'descr' => 'FAILED'],
            ]));
        }

        return $sensors;
    }
}
