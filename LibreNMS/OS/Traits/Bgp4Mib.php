<?php

/**
 * Bgp4Mib.php
 *
 * BGP session discovery from BGP4-MIB, and the helpers vendor session tables share.
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Traits;

use App\Facades\PortCache;
use App\Models\BgpPeer;
use App\Models\BgpPeerCbgp;
use Illuminate\Support\Collection;
use LibreNMS\Util\Bgp;
use LibreNMS\Util\IP;
use SnmpQuery;

trait Bgp4Mib
{
    /** BgpPeer column to BGP4-MIB column */
    private const BGP4_MIB_COLUMNS = [
        'bgpPeerRemoteAs' => 'bgpPeerRemoteAs',
        'bgpPeerState' => 'bgpPeerState',
        'bgpPeerAdminStatus' => 'bgpPeerAdminStatus',
        'bgpLocalAddr' => 'bgpPeerLocalAddr',
        'bgpPeerLastError' => 'bgpPeerLastError',
        'bgpPeerInUpdates' => 'bgpPeerInUpdates',
        'bgpPeerOutUpdates' => 'bgpPeerOutUpdates',
        'bgpPeerInTotalMessages' => 'bgpPeerInTotalMessages',
        'bgpPeerOutTotalMessages' => 'bgpPeerOutTotalMessages',
        'bgpPeerFsmEstablishedTime' => 'bgpPeerFsmEstablishedTime',
        'bgpPeerInUpdateElapsedTime' => 'bgpPeerInUpdateElapsedTime',
    ];

    /** BgpPeer columns that hold integers */
    private const BGP_INTEGER_COLUMNS = ['bgpPeerRemoteAs', 'bgpPeerLastErrorCode', 'bgpPeerLastErrorSubCode', 'bgpPeerFsmEstablishedTime', 'bgpPeerInUpdateElapsedTime'];

    /** BgpPeer columns that hold 32 bit counters */
    private const BGP_COUNTER_COLUMNS = ['bgpPeerInUpdates', 'bgpPeerOutUpdates', 'bgpPeerInTotalMessages', 'bgpPeerOutTotalMessages'];

    /**
     * BGP4-MIB has no VRF index, so it is read in each VRF context
     *
     * @return Collection<int, BgpPeer>
     */
    public function discoverBgpPeers(): Collection
    {
        return collect($this->getDevice()->getVrfContexts())->flatMap(fn (string $context) => $this->discoverBgp4MibPeers($context))->values();
    }

    /**
     * BGP4-MIB only holds a 2-byte AS, OS implementations may read their own tables when it is AS_TRANS
     */
    public function discoverBgpLocalAs(): ?int
    {
        return Bgp::localAs([SnmpQuery::get('BGP4-MIB::bgpLocalAs.0')->value()]);
    }

    /**
     * @return Collection<int, BgpPeer>
     */
    protected function discoverBgp4MibPeers(string $context): Collection
    {
        $vrfId = $this->getDevice()->vrfIdForContext($context);

        return collect($this->walkBgpTable($context, 'BGP4-MIB', self::BGP4_MIB_COLUMNS))->map(function (array $columns, int|string $index) use ($context, $vrfId): ?BgpPeer {
            $parts = explode('.', (string) $index);

            return $this->makeBgpPeer(Bgp::shiftIp($parts), null, $context, $vrfId, $columns);
        })->filter()->values();
    }

    /**
     * The first usable AS of a vendor table, when the local AS is not known or AS_TRANS
     */
    protected function bgpLocalAsFallback(?int $localAs, string $oid): ?int
    {
        if ($localAs !== null && $localAs != Bgp::AS_TRANS) {
            return $localAs;
        }

        return Bgp::localAs([...array_values(SnmpQuery::walk($oid)->values()), $localAs]);
    }

    /**
     * Walk the columns of a table, rows are keyed by numeric index and hold the values by model column.
     * The first column is walked alone, so devices without the table only cost a single walk.
     *
     * @param  array<string, string>  $columns  model column to table column
     * @return array<string, array<string, string>>
     */
    protected function walkBgpTable(string $context, string $mib, array $columns): array
    {
        $oids = array_map(fn (string $column) => "$mib::$column", array_values(array_unique($columns)));
        $first = SnmpQuery::context($context)->hideMib()->enumStrings()->numericIndex()->walk(array_shift($oids))->throwIfConnectionError();
        if (! $first->isValid()) {
            return [];
        }

        $values = $first->valuesByIndex();
        if (! empty($oids)) {
            SnmpQuery::context($context)->hideMib()->enumStrings()->numericIndex()->walk($oids)->valuesByIndex($values);
        }

        $rows = [];
        foreach ($values as $index => $row) {
            foreach ($columns as $modelColumn => $column) {
                if (isset($row[$column]) && $row[$column] !== '') {
                    $rows[$index][$modelColumn] = $row[$column];
                }
            }
        }

        return $rows;
    }

    /**
     * Build a session from snmp values. Sessions are identified by address, or by interface when
     * the address is only unique on its link or the session is unnumbered.
     * Null when the session has no remote AS, or no usable address or interface.
     *
     * @param  int|string|null  $iface  ifIndex or ifName
     * @param  array<string, mixed>  $columns  snmp values by BgpPeer column, bgpPeerLastError is split into code and subcode
     */
    protected function makeBgpPeer(?IP $address, int|string|null $iface, string $context, ?int $vrfId, array $columns): ?BgpPeer
    {
        $iface = $iface == '0' ? null : $iface; // 0 is never a valid ifIndex
        $identifier = $this->bgpPeerIdentifier($address, $iface);
        if ($identifier === null) {
            return null;
        }

        if (isset($columns['bgpPeerLastError'])) {
            $columns += Bgp::lastError($columns['bgpPeerLastError']);
        }
        unset($columns['bgpPeerLastError']);

        foreach ($columns as $column => $value) {
            $columns[$column] = match (true) {
                $column == 'bgpPeerState' => Bgp::state($value),
                $column == 'bgpLocalAddr' => IP::fromHexString($value, true)?->uncompressed(),
                in_array($column, self::BGP_INTEGER_COLUMNS) => Bgp::integer($value),
                in_array($column, self::BGP_COUNTER_COLUMNS) => Bgp::counter($value),
                default => $value,
            };
        }

        $peer = new BgpPeer(array_filter($columns, fn ($value) => $value !== null));
        if ($peer->bgpPeerRemoteAs === null) {
            return null;
        }

        $peer->bgpPeerIdentifier = $identifier;
        $peer->bgpPeerRemoteAddr = $address?->uncompressed() ?? '';
        $peer->context_name = $context;
        $peer->vrf_id = $vrfId;
        $peer->bgpPeerIface = $this->bgpPeerIfIndex($iface, $peer);

        return $peer;
    }

    /**
     * @param  int|string|null  $iface  ifIndex or ifName
     * @param  array<string, mixed>  $counters  snmp values by BgpPeerCbgp column
     */
    protected function makeBgpAddressFamily(?IP $address, int|string|null $iface, string $context, int|string $afi, int|string $safi, array $counters): ?BgpPeerCbgp
    {
        $identifier = $this->bgpPeerIdentifier($address, $iface == '0' ? null : $iface);
        if ($identifier === null) {
            return null;
        }

        $family = new BgpPeerCbgp(array_filter(array_map(Bgp::counter(...), $counters), fn ($value) => $value !== null));
        $family->bgpPeerIdentifier = $identifier;
        $family->context_name = $context;
        $family->afi = Bgp::afi($afi);
        $family->safi = Bgp::safi($safi);

        return $family;
    }

    /**
     * The VRF of tables that report every VRF from the default context
     */
    protected function bgpVrfId(int|string|null $vrfOid = null, ?string $vrfName = null): ?int
    {
        if ($vrfName !== null) {
            return $this->getDevice()->vrfs->firstWhere('vrf_name', $vrfName)?->vrf_id;
        }

        return $vrfOid === null ? null : $this->getDevice()->vrfs->firstWhere('vrf_oid', (string) $vrfOid)?->vrf_id;
    }

    /**
     * @param  int|string|null  $iface  ifIndex or ifName
     */
    private function bgpPeerIdentifier(?IP $address, int|string|null $iface): ?string
    {
        if ($iface !== null && ($address === null || $address->isLinkLocal())) {
            return is_numeric($iface) ? PortCache::getNameFromIfIndex((int) $iface, $this->getDevice()) ?? (string) $iface : (string) $iface;
        }

        $identifier = $address?->compressed();

        return in_array($identifier, [null, '0.0.0.0', '::']) ? null : $identifier;
    }

    /**
     * Tables may report the interface as ifIndex or ifName, otherwise find it by the session's local address
     *
     * @param  int|string|null  $iface  ifIndex or ifName
     */
    private function bgpPeerIfIndex(int|string|null $iface, BgpPeer $peer): ?int
    {
        if (is_numeric($iface)) {
            return (int) $iface;
        }

        $ifIndex = $iface ? PortCache::getByIfName($iface, $this->getDevice())?->ifIndex : null;
        if ($ifIndex !== null) {
            return $ifIndex;
        }

        $ip = IP::parse((string) $peer->bgpLocalAddr, true);
        if ($ip === null || in_array($ip->compressed(), ['0.0.0.0', '::'])) {
            return null;
        }

        $portId = PortCache::getIdFromIp($ip, $peer->context_name, $this->getDevice());

        return $portId ? PortCache::get($portId)?->ifIndex : null;
    }
}
