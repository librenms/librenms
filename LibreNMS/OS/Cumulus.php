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
use App\Models\Vrf;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use LibreNMS\Util\Bgp;
use LibreNMS\Util\IP;
use SnmpQuery;

class Cumulus extends \LibreNMS\OS
{
    /** BgpPeer column to the column of both CUMULUS-BGPVRF-MIB and CUMULUS-BGPUN-MIB */
    private const BGP_COLUMNS = [
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
}
