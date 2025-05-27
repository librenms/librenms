<?php

/**
 * Route.php
 *
 * Route discovery module
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link       http://librenms.org
 *
 * @copyright  2025 Peca Nesovanovic
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

namespace LibreNMS\Modules;

use App\Facades\PortCache;
use App\Models\Device;
use App\Models\Route;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Config;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\RouteDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\Util\IPv4;
use LibreNMS\Util\IPv6;
use SnmpQuery;

class Routes implements Module
{
    use SyncsModels;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return ['ports'];
    }

    /**
     * @inheritDoc
     */
    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * @inheritDoc
     */
    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $update_timestamp = \Carbon\Carbon::now();
        $max_routes = (null != Config::get('routes_max_number')) ? Config::get('routes_max_number') : 1000;

        $routesFromOs = new Collection;
        $routesFromDiscovery = new Collection;

        if ($os instanceof RouteDiscovery) {
            $routesFromOs = $os->discoverRoutes();
        }

        if ($routesFromOs->isEmpty()) {
            $routesFromDiscovery = $this->discoverInetCidrRoutes($os->getDevice(), $max_routes);

            $routesFromDiscovery = $routesFromDiscovery->merge($this->discoverIpCidrRoutes($os->getDevice(), $max_routes));
            $routesFromDiscovery = $routesFromDiscovery->merge($this->discoverRfcRoutes($os->getDevice()));
            $routesFromDiscovery = $routesFromDiscovery->merge($this->discoverIpv6MibRoutes($os->getDevice()));
            $routesFromDiscovery = $routesFromDiscovery->merge($this->discoverVpnVrfRoutes($os->getDevice(), $max_routes));
        }

        $routes = $routesFromOs->merge($routesFromDiscovery)->filter(function ($data) use ($update_timestamp) {
            $dst = trim(str_replace('"', '', $data->inetCidrRouteDest ?? ''));
            $hop = trim(str_replace('"', '', $data->inetCidrRouteNextHop ?? ''));
            $context = trim(str_replace('"', '', $data->context_name ?? ''));
            $prefix = trim($data->inetCidrRoutePfxLen ?? '');
            $dstType = trim($data->inetCidrRouteDestType ?? '');

            if ($dst == '' || $hop == '') { // missing crucial data
                Log::info('incomplete: ' . $dst . ' - ' . $hop . ' - ' . $prefix);

                return null;
            }

            if ($prefix == '' && $dstType == 'ipv4') { //Computing Classfull Netmask
                $tmp = explode('.', $dst);
                if ($tmp[0] < 128) {
                    $prefix = '255.0.0.0';
                }
                if ($tmp[0] > 128 && $tmp[0] < 192) {
                    $prefix = '255.255.0.0';
                }
                if ($tmp[0] > 192 && $tmp[0] < 224) {
                    $prefix = '255.255.255.0';
                }
                Log::info('Classfull netmask from RFC: ' . $dst . ' - ' . $hop . ' - ' . $prefix);
            }

            try {
                preg_match('/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$/', (string) $prefix, $tmp); // is it netmask or cidr?
                $pfxLen = (empty($tmp[1])) ? intval($data->inetCidrRoutePfxLen) : IPv4::netmask2cidr($tmp[1]);
                Log::debug('try: ' . $dst . ' - ' . $hop . ' - ' . $pfxLen);

                if ($dstType == 'ipv6') {
                    $dst = IPv6::fromHexString($dst)->uncompressed();
                    $hop = IPv6::fromHexString($hop)->uncompressed();
                } elseif ($dstType == 'ipv4') {
                    $tst = new IPv4($dst . '/' . $pfxLen);
                } else {
                    return null;
                }
            } catch (InvalidIpException $e) {
                Log::error('Failed to parse IP: ' . $e->getMessage());

                return null;
            }

            if ($dst == 'fe80:0000:0000:0000:0000:0000:0000:0000') { // skip IPv6 LLA
                return null;
            }

            $data->updated_at = $update_timestamp;
            $data->port_id = $data->port_id ?? 0;
            $data->context_name = $context;
            $data->inetCidrRouteIfIndex = intval($data->inetCidrRouteIfIndex);
            $data->inetCidrRouteType = intval($data->inetCidrRouteType);
            $data->inetCidrRouteProto = intval($data->inetCidrRouteProto);
            $data->inetCidrRouteNextHopAS = intval($data->inetCidrRouteNextHopAS);
            $data->inetCidrRouteMetric1 = intval($data->inetCidrRouteMetric1) < 0 ? 0 : intval($data->inetCidrRouteMetric1); //negative val from RFC1213
            $data->inetCidrRouteDestType = $data->inetCidrRouteDestType ?? '';
            $data->inetCidrRouteDest = $dst;
            $data->inetCidrRouteNextHopType = $data->inetCidrRouteNextHopType ?? '';
            $data->inetCidrRouteNextHop = $hop;
            $data->inetCidrRoutePolicy = $data->inetCidrRoutePolicy ?? '';
            $data->inetCidrRoutePfxLen = $pfxLen;

            return $data;
        });

        ModuleModelObserver::observe(Route::class);

        $routesExisting = $os->getDevice()->routes()->get();
        $this->syncModels($os->getDevice(), 'routes', $this->fillNew($routesExisting, $routes));
        // We add (or update) routes, but old ones are kept for history
        // Cleaning is done in `daily`.
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
        return $device->routes()->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->routes()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        if ($type == 'polling') {
            return null;
        }

        return [
            'route' => $device->routes()->get()->map->makeHidden([
                'route_id', 'created_at', 'updated_at', 'laravel_through_key']),
        ];
    }

    private function discoverInetCidrRoutes(Device $device, $max_routes): Collection
    {
        $nrOfRoutesFromDevice = intval(SnmpQuery::hideMib()->get('IP-FORWARD-MIB::inetCidrRouteNumber.0')->value());

        if (! empty($nrOfRoutesFromDevice) && $nrOfRoutesFromDevice < $max_routes) {
            Log::info('IP-FORWARD-MIB::inetCidrRouteTable');

            return SnmpQuery::hideMib()->walk(['IP-FORWARD-MIB::inetCidrRouteTable'])
                ->mapTable(function ($data, $dstType = '', $dst = '', $pfxLen = '', $policy = '', $hopType = '', $hop = '') use ($device) {
                    return new Route([
                        'port_id' => PortCache::getIdFromIfIndex($data['inetCidrRouteIfIndex'] ?? 0, $device->device_id) ?? 0,
                        'context_name' => '',
                        'inetCidrRouteIfIndex' => $data['inetCidrRouteIfIndex'] ?? 0,
                        'inetCidrRouteType' => $data['inetCidrRouteType'] ?? 0,
                        'inetCidrRouteProto' => $data['inetCidrRouteProto'] ?? 0,
                        'inetCidrRouteNextHopAS' => $data['inetCidrRouteNextHopAS'] ?? 0,
                        'inetCidrRouteMetric1' => $data['inetCidrRouteMetric1'] ?? 0,
                        'inetCidrRouteDestType' => $dstType,
                        'inetCidrRouteDest' => $dst,
                        'inetCidrRouteNextHopType' => $hopType,
                        'inetCidrRouteNextHop' => $hop,
                        'inetCidrRoutePolicy' => $policy,
                        'inetCidrRoutePfxLen' => $pfxLen,
                    ]);
                })->filter();
        } else {
            Log::info('Skipping "inetCidrRouteTable"');

            return new Collection;
        }
    }

    private function discoverIpCidrRoutes(Device $device, $max_routes): Collection
    {
        $nrOfRoutesFromDevice = intval(SnmpQuery::hideMib()->get('IP-FORWARD-MIB::ipCidrRouteNumber.0')->value());

        if (! empty($nrOfRoutesFromDevice) && $nrOfRoutesFromDevice < $max_routes) {
            Log::info('IP-FORWARD-MIB::ipCidrRouteTable');

            return SnmpQuery::hideMib()->walk(['IP-FORWARD-MIB::ipCidrRouteTable'])
            ->mapTable(function ($data, $dst = '', $netmask = '', $tos = '', $hop = '') use ($device) {
                return new Route([
                    'port_id' => PortCache::getIdFromIfIndex($data['ipCidrRouteIfIndex'] ?? 0, $device->device_id) ?? 0,
                    'context_name' => '',
                    'inetCidrRouteIfIndex' => $data['ipCidrRouteIfIndex'] ?? 0,
                    'inetCidrRouteType' => $data['ipCidrRouteType'] ?? 0,
                    'inetCidrRouteProto' => $data['ipCidrRouteProto'] ?? 0,
                    'inetCidrRouteNextHopAS' => $data['ipCidrRouteNextHopAS'] ?? 0,
                    'inetCidrRouteMetric1' => $data['ipCidrRouteMetric1'] ?? 0,
                    'inetCidrRouteDestType' => 'ipv4',
                    'inetCidrRouteDest' => $dst,
                    'inetCidrRouteNextHopType' => 'ipv4',
                    'inetCidrRouteNextHop' => $hop,
                    'inetCidrRoutePolicy' => $data['ipCidrRouteInfo'] ?? '',
                    'inetCidrRoutePfxLen' => $netmask,
                ]);
            })->filter();
        } else {
            Log::info('Skipping "ipCidrRouteTable"');

            return new Collection;
        }
    }

    private function discoverRfcRoutes(Device $device): Collection
    {
        Log::info('RFC1213-MIB::ipRouteTable');

        return SnmpQuery::hideMib()->walk(['RFC1213-MIB::ipRouteTable'])
        ->mapTable(function ($data) use ($device) {
            return new Route([
                'port_id' => PortCache::getIdFromIfIndex($data['ipRouteIfIndex'] ?? 0, $device->device_id) ?? 0,
                'context_name' => '',
                'inetCidrRouteType' => $data['ipRouteType'] ?? 0,
                'inetCidrRouteProto' => $data['ipRouteProto'] ?? 0,
                'inetCidrRouteIfIndex' => $data['ipRouteIfIndex'] ?? 0,
                'inetCidrRouteNextHopAS' => '0',
                'inetCidrRouteMetric1' => $data['ipRouteMetric1'] ?? 0,
                'inetCidrRouteDestType' => 'ipv4',
                'inetCidrRouteDest' => $data['ipRouteDest'] ?? '',
                'inetCidrRouteNextHopType' => 'ipv4',
                'inetCidrRouteNextHop' => $data['ipRouteNextHop'] ?? '',
                'inetCidrRoutePfxLen' => $data['ipRouteMask'] ?? '',
                'inetCidrRoutePolicy' => $data['ipRouteInfo'] ?? '',
            ]);
        })->filter();
    }

    private function discoverIpv6MibRoutes(Device $device): Collection
    {
        Log::info('IPV6-MIB::ipv6RouteTable');

        return SnmpQuery::hideMib()->walk(['IPV6-MIB::ipv6RouteTable'])
        ->mapTable(function ($data, $dst = '', $pfxLen = '', $tos = '') use ($device) {
            return new Route([
                'port_id' => PortCache::getIdFromIfIndex($data['ipv6RouteIfIndex'] ?? 0, $device->device_id) ?? 0,
                'context_name' => '',
                'inetCidrRouteIfIndex' => $data['ipv6RouteIfIndex'] ?? 0,
                'inetCidrRouteType' => $data['ipv6RouteType'] ?? 0,
                'inetCidrRouteProto' => $data['ipv6RouteProtocol'] ?? 0,
                'inetCidrRouteNextHopAS' => '0',
                'inetCidrRouteMetric1' => $data['ipv6RouteMetric'] ?? 0,
                'inetCidrRouteDestType' => 'ipv6',
                'inetCidrRouteDest' => $dst,
                'inetCidrRouteNextHopType' => 'ipv6',
                'inetCidrRouteNextHop' => $data['ipv6RouteNextHop'] ?? '',
                'inetCidrRoutePolicy' => $data['ipv6RoutePolicy'] ?? '',
                'inetCidrRoutePfxLen' => $pfxLen,
            ]);
        })->filter();
    }

    private function discoverVpnVrfRoutes(Device $device, $max_routes): Collection
    {
        Log::info('MPLS-L3VPN-STD-MIB');

        foreach (SnmpQuery::hideMib()->walk(['MPLS-L3VPN-STD-MIB::mplsL3VpnVrfPerfCurrNumRoutes'])->table(1) as $vpnId => $data) {
            if (! empty($data['mplsL3VpnVrfPerfCurrNumRoutes'])) {
                if ($data['mplsL3VpnVrfPerfCurrNumRoutes'] > $max_routes) {
                    Log::info('Skipping all MPLS routes because vpn instance ' . $vpnId . ' has more than ' . $max_routes . ' routes');

                    return new Collection;
                }
            }
        }

        return SnmpQuery::hideMib()->walk(['MPLS-L3VPN-STD-MIB::mplsL3VpnVrfRteTable'])
        ->mapTable(function ($data, $vpnId, $dstType = '', $dst = '', $pfxLen = '', $policy = '', $hopType = '', $hop = '') use ($device) {
            return new Route([
                'port_id' => PortCache::getIdFromIfIndex($data['mplsL3VpnVrfRteInetCidrIfIndex'] ?? 0, $device->device_id) ?? 0,
                'context_name' => $vpnId,
                'inetCidrRouteIfIndex' => $data['mplsL3VpnVrfRteInetCidrIfIndex'] ?? 0,
                'inetCidrRouteType' => $data['mplsL3VpnVrfRteInetCidrType'] ?? 0,
                'inetCidrRouteProto' => $data['mplsL3VpnVrfRteInetCidrProto'] ?? 0,
                'inetCidrRouteNextHopAS' => $data['mplsL3VpnVrfRteInetCidrNextHopAS'] ?? 0,
                'inetCidrRouteMetric1' => $data['mplsL3VpnVrfRteInetCidrMetric1'] ?? 0,
                'inetCidrRouteDestType' => $dstType,
                'inetCidrRouteDest' => $dst,
                'inetCidrRouteNextHopType' => $hopType,
                'inetCidrRouteNextHop' => $hop,
                'inetCidrRoutePolicy' => $policy,
                'inetCidrRoutePfxLen' => $pfxLen,
            ]);
        })->filter();
    }
}
