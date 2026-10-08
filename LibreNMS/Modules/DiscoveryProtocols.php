<?php

/**
 * DiscoveryProtocols.php
 *
 * Discover links to neighbors via LLDP, CDP, and FDP and autodiscover new devices
 * found through discovery protocols and OSPF.
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

namespace LibreNMS\Modules;

use App\Actions\Device\AutoDiscoverDevice;
use App\Facades\LibrenmsConfig;
use App\Facades\PortCache;
use App\Models\Device;
use App\Models\Link;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Discovery\Neighbors\Neighbor;
use LibreNMS\Discovery\Neighbors\NeighborFinder;
use LibreNMS\Enum\LldpPortIdSubtype;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ConnectivityHelper;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\Util\IP;
use LibreNMS\Util\Mac;

class DiscoveryProtocols implements Module
{
    use SyncsModels;

    private NeighborFinder $finder;
    /** @var array<string, Device|null> autodiscovery results by target, so each target is only tried once */
    private array $autodiscovered = [];

    /**
     * @return string[]
     */
    public function dependencies(): array
    {
        return ['ports'];
    }

    /**
     * @inheritDoc
     */
    public function shouldDiscover(OS $os, ModuleStatus $status, ConnectivityHelper $connectivity): bool
    {
        return $status->isEnabled() && $connectivity->snmpIsAvailable();
    }

    /**
     * @inheritDoc
     */
    public function shouldPoll(OS $os, ModuleStatus $status, ConnectivityHelper $connectivity): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $device = $os->getDevice();
        $this->finder = new NeighborFinder;
        $this->autodiscovered = [];

        $neighbors = $os->discoverNeighbors();
        foreach ($neighbors->countBy('protocol') as $protocol => $count) {
            Log::info(strtoupper($protocol) . ": $count neighbors");
        }

        $links = $neighbors
            ->map(fn (Neighbor $neighbor) => $this->makeLink($neighbor, $device))
            ->filter();

        ModuleModelObserver::observe(Link::class, 'Links');
        $this->syncModels($device, 'links', $links, $device->links()->get());
        ModuleModelObserver::done();

        Link::deleteOrphans();

        if (LibrenmsConfig::get('autodiscovery.ospf') === true) {
            $this->autoDiscoverIps($device, $device->ospfNbrs()->distinct()->pluck('ospfNbrIpAddr'), 'OSPF');
        }

        if (LibrenmsConfig::get('autodiscovery.ospfv3') === true) {
            $this->autoDiscoverIps($device, $device->ospfv3Nbrs()->distinct()->pluck('ospfv3NbrAddress'), 'OSPFv3');
        }
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        // no polling
    }

    /**
     * @inheritDoc
     */
    public function dataExists(Device $device): bool
    {
        return $device->links()->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->links()->delete();
    }

    /**
     * @return array{links: Collection<int, Link>}|null
     */
    public function dump(Device $device, string $type): ?array
    {
        if ($type == 'poller') {
            return null;
        }

        return [
            'links' => $device->links()
                ->leftJoin('ports', 'links.local_port_id', 'ports.port_id')
                ->select(['links.*', 'ports.ifAlias', 'ports.ifDescr', 'ports.ifName'])
                ->orderBy('ports.ifAlias')->orderBy('ports.ifDescr')->orderBy('ports.ifName')
                ->orderBy('remote_hostname')->orderBy('remote_version')->orderBy('remote_port')
                ->get()->map->makeHidden(['id', 'local_device_id', 'local_port_id', 'remote_device_id', 'remote_port_id']),
        ];
    }

    private function makeLink(Neighbor $neighbor, Device $device): ?Link
    {
        // find or add the device even when the link can't be created
        $remoteDevice = $this->finder->findDevice($neighbor) ?? $this->autoDiscover($neighbor, $device);

        if ($neighbor->localPortId === null) {
            Log::debug("Skipping $neighbor->protocol neighbor $neighbor->sysName: local port not found");

            return null;
        }

        $remotePort = $remoteDevice ? $this->finder->findPort($neighbor, $remoteDevice) : null;
        $remoteHostname = $neighbor->sysName
            ?: ($remoteDevice ? ($remoteDevice->sysName ?: $remoteDevice->hostname) : '')
            ?: $neighbor->sysDescr;

        if ($remoteHostname === '') {
            Log::debug("Skipping $neighbor->protocol neighbor on port $neighbor->localPortId: no name");

            return null;
        }

        return new Link([
            'local_port_id' => $neighbor->localPortId,
            'local_device_id' => $device->device_id,
            'protocol' => mb_substr($neighbor->protocol, 0, 11),
            'remote_hostname' => mb_substr($remoteHostname, 0, 128),
            'remote_device_id' => $remoteDevice->device_id ?? 0,
            'remote_port_id' => $remotePort?->port_id,
            'remote_port' => mb_substr($this->remotePortLabel($neighbor), 0, 128),
            'remote_platform' => $neighbor->platform === null ? null : mb_substr($neighbor->platform, 0, 256),
            'remote_version' => mb_substr($neighbor->sysDescr, 0, 256),
        ]);
    }

    /**
     * Human-readable name of the neighbor's port
     */
    private function remotePortLabel(Neighbor $neighbor): string
    {
        if ($neighbor->portIdSubtype === LldpPortIdSubtype::MacAddress && $neighbor->portMac !== null) {
            return Mac::parse($neighbor->portMac)->readable();
        }

        return $neighbor->portId !== '' ? $neighbor->portId : $neighbor->portDescr;
    }

    /**
     * Try to add the neighbor as a new device by name, then by ip
     */
    private function autoDiscover(Neighbor $neighbor, Device $device): ?Device
    {
        if (LibrenmsConfig::get('autodiscovery.xdp') !== true || $this->isExcludedFromAutodiscovery($neighbor)) {
            return null;
        }

        $targets = [$neighbor->sysName];
        if (LibrenmsConfig::get('discovery_by_ip', false)) {
            $targets[] = $neighbor->managementIp;
        }

        foreach (array_filter($targets) as $target) {
            // normalize so the same target written differently is only tried once
            $key = IP::parse($target, true)?->compressed() ?? strtolower(rtrim($target, '.'));

            if (! array_key_exists($key, $this->autodiscovered)) {
                $port = PortCache::get($neighbor->localPortId);
                $this->autodiscovered[$key] = app(AutoDiscoverDevice::class)->execute($target, $device, strtoupper($neighbor->protocol), $port);
            }

            if ($this->autodiscovered[$key]) {
                return $this->autodiscovered[$key];
            }
        }

        return null;
    }

    private function isExcludedFromAutodiscovery(Neighbor $neighbor): bool
    {
        $checks = [
            'autodiscovery.xdp_exclude.sysname_regexp' => $neighbor->sysName,
            'autodiscovery.xdp_exclude.sysdesc_regexp' => $neighbor->sysDescr,
            'autodiscovery.cdp_exclude.platform_regexp' => $neighbor->platform,
        ];

        foreach ($checks as $setting => $value) {
            if (! $value) {
                continue;
            }

            foreach ((array) LibrenmsConfig::get($setting) as $regex) {
                if (preg_match($regex . 'i', $value)) {
                    Log::debug("$neighbor->sysName - regexp '$regex' matches '$value' - skipping device discovery");

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  Collection<int, string>  $addresses
     */
    private function autoDiscoverIps(Device $device, Collection $addresses, string $method): void
    {
        Log::info("$method autodiscovery: " . $addresses->count() . ' neighbors');

        foreach ($addresses as $address) {
            $ip = IP::parse($address, true);

            // check networks here to avoid unnecessary reverse dns lookups
            if ($ip === null
                || $ip->inNetworks(LibrenmsConfig::get('autodiscovery.nets-exclude'))
                || ! $ip->inNetworks(LibrenmsConfig::get('nets'))
                || Device::findByIp((string) $ip) !== null) {
                continue;
            }

            app(AutoDiscoverDevice::class)->execute(gethostbyaddr((string) $ip) ?: (string) $ip, $device, $method);
        }
    }
}
