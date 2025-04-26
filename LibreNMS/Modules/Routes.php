<?php

namespace LibreNMS\Modules;

use App\Facades\PortCache;
use App\Models\Device;
use App\Models\Route;
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
        $routesFromDb = Route::where('device_id', $os->getDeviceId())->get()->toArray();

        $routesFromOs = new Collection;
        $routesFromInetCidr = new Collection;
        $routesFromIpv6Mib = new Collection;
        $routesFromIpCidr = new Collection;
        $routesFromRfc = new Collection;
        $routesFromVpnVrf = new Collection;

        if ($os instanceof RouteDiscovery) {
            $routesFromOs = $os->discoverRoutes();
        }

        if ($routesFromOs->isEmpty()) {
            $routesFromInetCidr = $this->discoverInetCidrRoutes($os->getDevice());
            $routesFromRfc = $this->discoverRfcRoutes($os->getDevice());
            $routesFromIpCidr = $this->discoverIpCidrRoutes($os->getDevice());
            $routesFromIpv6Mib = $this->discoverIpv6MibRoutes($os->getDevice());
            $routesFromVpnVrf = $this->discoverVpnVrfRoutes($os->getDevice());
        }

        $entries = array_merge(
            $routesFromOs->toArray(),
            $routesFromInetCidr->toArray(),
            $routesFromRfc->toArray(),
            $routesFromIpCidr->toArray(),
            $routesFromIpv6Mib->toArray(),
            $routesFromVpnVrf->toArray(),
        );

        //remove duplicate routes from discovery
        foreach ($entries as $eIndex => $data) {
            if (count(array_filter($entries, function ($eData) use ($data) {
                return
                    $eData['port_id'] == $data['port_id'] &&
                    $eData['context_name'] == $data['context_name'] &&
                    $eData['inetCidrRouteIfIndex'] == $data['inetCidrRouteIfIndex'] &&
                    $eData['inetCidrRouteDest'] == $data['inetCidrRouteDest'] &&
                    $eData['inetCidrRouteNextHop'] == $data['inetCidrRouteNextHop'] &&
                    $eData['inetCidrRoutePfxLen'] == $data['inetCidrRoutePfxLen'];
            })) > 1) {
                unset($entries[$eIndex]);
            }
        }

        //remove duplicate routes from DB
        $deleteIds = [];
        foreach ($routesFromDb as $dbIndex => $dbData) {
            if (count(array_filter($routesFromDb, function ($eData) use ($dbData) {
                return
                    $eData['port_id'] == $dbData['port_id'] &&
                    $eData['context_name'] == $dbData['context_name'] &&
                    $eData['inetCidrRouteIfIndex'] == $dbData['inetCidrRouteIfIndex'] &&
                    $eData['inetCidrRouteDest'] == $dbData['inetCidrRouteDest'] &&
                    $eData['inetCidrRouteNextHop'] == $dbData['inetCidrRouteNextHop'] &&
                    $eData['inetCidrRoutePfxLen'] == $dbData['inetCidrRoutePfxLen'];
            })) > 1) {
                $deleteIds[] = $dbData['route_id'];
                unset($routesFromDb[$dbIndex]);
            }
        }

        //find new routes
        $newRow = [];
        foreach ($entries as $eIndex => $eData) {
            if (! array_filter($routesFromDb, function ($dbData) use ($eData) {
                return
                    $dbData['port_id'] == $eData['port_id'] &&
                    $dbData['context_name'] == $eData['context_name'] &&
                    $dbData['inetCidrRouteIfIndex'] == $eData['inetCidrRouteIfIndex'] &&
                    $dbData['inetCidrRouteDest'] == $eData['inetCidrRouteDest'] &&
                    $dbData['inetCidrRouteNextHop'] == $eData['inetCidrRouteNextHop'] &&
                    $dbData['inetCidrRoutePfxLen'] == $eData['inetCidrRoutePfxLen'];
            })) {
                $newRow[] = $eData;
            }
        }

        Log::info('Processing routes DB');
        foreach ($deleteIds as $id) {
            Route::where('route_id', $id)->delete();
            echo '-';
        }

        $goodIds = [];
        foreach (array_merge($newRow, $routesFromDb) as $key => $data) {
            $data['created_at'] = $data['created_at'] ?? $update_timestamp;
            $data['updated_at'] = $update_timestamp;

            $dbRoute = Route::updateOrCreate([
                'device_id' => $os->getDeviceId(),
                'port_id' => $data['port_id'] ?? 0,
                'context_name' => $data['context_name'] ?? '',
                'inetCidrRouteIfIndex' => intval($data['inetCidrRouteIfIndex']),
                'inetCidrRouteDest' => $data['inetCidrRouteDest'] ?? '',
                'inetCidrRouteNextHop' => $data['inetCidrRouteNextHop'] ?? '',
                'inetCidrRoutePfxLen' => intval($data['inetCidrRoutePfxLen']),
            ], [
                'created_at' => $data['created_at'],
                'updated_at' => $data['updated_at'],
                'inetCidrRouteMetric1' => intval($data['inetCidrRouteMetric1']),
                'inetCidrRouteType' => intval($data['inetCidrRouteType']),
                'inetCidrRouteProto' => intval($data['inetCidrRouteProto']),
                'inetCidrRouteNextHopAS' => intval($data['inetCidrRouteNextHopAS']),
                'inetCidrRouteDestType' => $data['inetCidrRouteDestType'] ?? '',
                'inetCidrRouteNextHopType' => $data['inetCidrRouteNextHopType'] ?? '',
                'inetCidrRoutePolicy' => $data['inetCidrRoutePolicy'] ?? '',
            ]);
            $info = ($dbRoute->wasRecentlyCreated) ? '+' : '.';
            echo $info;
            $goodIds[] = $dbRoute->route_id;
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

    private function discoverInetCidrRoutes(Device $device): Collection
    {
        $max_routes = (null != Config::get('routes_max_number')) ? Config::get('routes_max_number') : 1000;
        Log::info('IP-FORWARD-MIB::inetCidrRouteTable');
        $nrOfRoutesFromDevice = intval(SnmpQuery::hideMib()->get('IP-FORWARD-MIB::inetCidrRouteNumber.0')->value());

        if ($nrOfRoutesFromDevice < $max_routes) {
            return SnmpQuery::hideMib()->walk(['IP-FORWARD-MIB::inetCidrRouteTable'])
                ->mapTable(function ($data, $dstType, $dst, $pfxLen, $policy, $hopType, $hop) use ($device) {
                    $dst = str_replace('"', '', $dst);
                    $hop = str_replace('"', '', $hop);
                    try {
                        if ($dstType == 'ipv6') {
                            $dstAddr = IPv6::fromHexString($dst);
                            $dst = $dstAddr->uncompressed();
                            $hopAddr = IPv6::fromHexString($hop);
                            $hop = $hopAddr->uncompressed();
                        } else {
                            $tst = new IPv4($dst . '/' . $pfxLen);
                        }

                        return new Route([
                            'port_id' => PortCache::getIdFromIfIndex($data['inetCidrRouteIfIndex'], $device->device_id) ?? 0,
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
                    } catch (InvalidIpException $e) {
                        Log::error('Failed to parse IP: ' . $e->getMessage());

                        return null;
                    }
                })->filter();
        } else {
            Log::info('Skipping  "inetCidrRouteTable" because device has more than ' . $max_routes . ' routes');

            return new Collection;
        }
    }

    private function discoverIpCidrRoutes(Device $device): Collection
    {
        $max_routes = (null != Config::get('routes_max_number')) ? Config::get('routes_max_number') : 1000;
        Log::info('IP-FORWARD-MIB::ipCidrRouteTable');
        $nrOfRoutesFromDevice = intval(SnmpQuery::hideMib()->get('IP-FORWARD-MIB::ipCidrRouteNumber.0')->value());

        if ($nrOfRoutesFromDevice < $max_routes) {
            return SnmpQuery::hideMib()->walk(['IP-FORWARD-MIB::ipCidrRouteTable'])
            ->mapTable(function ($data, $dst, $netmask, $tos, $hop) use ($device) {
                try {
                    $pfxLen = IPv4::netmask2cidr($netmask);
                    $tst = new IPv4($dst . '/' . $pfxLen);

                    return new Route([
                        'port_id' => PortCache::getIdFromIfIndex($data['ipCidrRouteIfIndex'], $device->device_id) ?? 0,
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
                        'inetCidrRoutePolicy' => $data['ipCidrRouteInfo'],
                        'inetCidrRoutePfxLen' => $pfxLen,
                    ]);
                } catch (InvalidIpException $e) {
                    Log::error('Failed to parse IP: ' . $e->getMessage());

                    return null;
                }
            })->filter();
        } else {
            Log::info('Skipping  "ipCidrRouteTable" because device has more than ' . $max_routes . ' routes');

            return new Collection;
        }
    }

    private function discoverRfcRoutes(Device $device): Collection
    {
        Log::info('RFC1213-MIB::ipRouteTable');

        return SnmpQuery::hideMib()->walk(['RFC1213-MIB::ipRouteTable'])
        ->mapTable(function ($data) use ($device) {
            if (! empty($data['ipRouteDest']) && ! empty($data['ipRouteNextHop']) && ! empty($data['ipRouteMask'])) {
                try {
                    $pfxLen = IPv4::netmask2cidr($data['ipRouteMask']);
                    $tst = new IPv4($data['ipRouteDest'] . '/' . $pfxLen);

                    return new Route([
                        'port_id' => PortCache::getIdFromIfIndex($data['ipRouteIfIndex'], $device->device_id) ?? 0,
                        'context_name' => '',
                        'inetCidrRouteType' => $data['ipRouteType'],
                        'inetCidrRouteProto' => $data['ipRouteProto'],
                        'inetCidrRouteIfIndex' => $data['ipRouteIfIndex'],
                        'inetCidrRouteNextHopAS' => '0',
                        'inetCidrRouteMetric1' => intval($data['ipRouteMetric1']) < 0 ? 0 : intval($data['ipRouteMetric1']),
                        'inetCidrRouteDestType' => 'ipv4',
                        'inetCidrRouteDest' => $data['ipRouteDest'],
                        'inetCidrRouteNextHopType' => 'ipv4',
                        'inetCidrRouteNextHop' => $data['ipRouteNextHop'],
                        'inetCidrRoutePfxLen' => $pfxLen,
                        'inetCidrRoutePolicy' => $data['ipRouteInfo'],
                    ]);
                } catch (InvalidIpException $e) {
                    Log::error('Failed to parse IP: ' . $e->getMessage());

                    return null;
                }
            }
        })->filter();
    }

    private function discoverIpv6MibRoutes(Device $device): Collection
    {
        Log::info('IPV6-MIB::ipv6RouteTable');

        return SnmpQuery::hideMib()->walk(['IPV6-MIB::ipv6RouteTable'])
        ->mapTable(function ($data, $dst, $pfxLen, $tos) use ($device) {
            try {
                $ipv6dst = IPv6::fromHexString($dst);
                $ipv6hop = IPv6::fromHexString($data['ipv6RouteNextHop']);

                return new Route([
                    'port_id' => PortCache::getIdFromIfIndex($data['ipv6RouteIfIndex'], $device->device_id) ?? 0,
                    'context_name' => '',
                    'inetCidrRouteIfIndex' => $data['ipv6RouteIfIndex'],
                    'inetCidrRouteType' => $data['ipv6RouteType'],
                    'inetCidrRouteProto' => $data['ipv6RouteProtocol'],
                    'inetCidrRouteNextHopAS' => '0',
                    'inetCidrRouteMetric1' => $data['ipv6RouteMetric'],
                    'inetCidrRouteDestType' => 'ipv6',
                    'inetCidrRouteDest' => $ipv6dst->uncompressed(),
                    'inetCidrRouteNextHopType' => 'ipv6',
                    'inetCidrRouteNextHop' => $ipv6hop->uncompressed(),
                    'inetCidrRoutePolicy' => $data['ipv6RoutePolicy'],
                    'inetCidrRoutePfxLen' => $pfxLen,
                ]);
            } catch (InvalidIpException $e) {
                Log::error('Failed to parse IP: ' . $e->getMessage());
            }
        })->filter();
    }

    private function discoverVpnVrfRoutes(Device $device): Collection
    {
        $max_routes = (null != Config::get('routes_max_number')) ? Config::get('routes_max_number') : 1000;
        Log::info('MPLS-L3VPN-STD-MIB');

        foreach (SnmpQuery::hideMib()->walk(['MPLS-L3VPN-STD-MIB::mplsL3VpnVrfPerfCurrNumRoutes'])->table(1) as $vpnId => $data) {
            //mes24xx
            if (empty($data['mplsL3VpnVrfPerfCurrNumRoutes'])) {
                Log::info('Skipping all MPLS routes because invalid MPLS-L3VPN-STD-MIB data');

                return new Collection;
            }
            if ($data['mplsL3VpnVrfPerfCurrNumRoutes'] > $max_routes) {
                Log::info('Skipping all MPLS routes because vpn instance ' . $vpnId . ' has more than ' . $max_routes . ' routes');

                return new Collection;
            }
        }

        return SnmpQuery::hideMib()->walk(['MPLS-L3VPN-STD-MIB::mplsL3VpnVrfRteTable'])
        ->mapTable(function ($data, $vpnId, $dstType, $dst, $pfxLen, $policy, $hopType, $hop) use ($device) {
            try {
                if ($dstType == 'ipv6') {
                    $dstAddr = IPv6::fromHexString($dst);
                    $dst = $dstAddr->uncompressed();
                    $hopAddr = IPv6::fromHexString($hop);
                    $hop = $hopAddr->uncompressed();
                } else {
                    $tst = new IPv4($dst . '/' . $pfxLen);
                }

                return new Route([
                    'port_id' => PortCache::getIdFromIfIndex($data['mplsL3VpnVrfRteInetCidrIfIndex'], $device->device_id) ?? 0,
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
            } catch (InvalidIpException $e) {
                Log::error('Failed to parse IP: ' . $e->getMessage());

                return null;
            }
        })->filter();
    }
}
