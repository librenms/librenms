<?php
/**
 * Ipv6Addresses.php
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
 * @copyright  2024 Peca Nesovanovic
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Ipv6Address;
use App\Models\Ipv6Network;
use App\Models\Port;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\Util\IPv6;
use Log;
use SnmpQuery;

class Ipv6Addresses implements Module
{
    use SyncsModels;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return ['ports'];
    }

    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * Discover this module. Heavier processes can be run here
     * Run infrequently (default 4 times a day)
     *
     * @param  OS  $os
     */
    public function discover(OS $os): void
    {
        $this->discoverIpv6($os);
    }

    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return false; // no polling
    }

    /**
     * Poll data for this module and update the DB / RRD.
     * Try to keep this efficient and only run if discovery has indicated there is a reason to run.
     * Run frequently (default every 5 minutes)
     *
     * @param  OS  $os
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        // not implemented
    }

    /**
     * Remove all DB data for this module.
     * This will be run when the module is disabled.
     */
    public function cleanup(Device $device): void
    {
        $deviceArr['device_id'] = $device->device_id;
        self::cleanupIpv6($deviceArr, []);
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device)
    {
        $fromDb = $device->ipv6()->orderBy('ipv6_address')->get();
        $netIDs = array_column($fromDb->toArray(), 'ipv6_network_id');

        return [
            'addresses' => $fromDb->map->makeHidden(['ipv6_address_id', 'ipv6_compressed', 'ipv6_network_id', 'port_id', 'laravel_through_key']),
            'networks' => Ipv6Network::whereIn('ipv6_network_id', $netIDs)->get()->map->makeHidden(['ipv6_network_id']),
        ];
    }

    private function discoverIpv6(OS $os): void
    {
        $device = $os->getDeviceArray();
        $specialOS = [
            'jetstream',
            'eltex-mes21xx',
            'eltex-mes23xx',
            'eltex-mes24xx',
        ];
        $func = (array_search($device['os'], $specialOS) !== false) ? $device['os'] : 'StandardOS';
        $func = str_replace('-', '_', 'discoverIpv6_' . $func);
        $valid = [];

        foreach ($os->getDevice()->getVrfContexts() as $context_name) {
            $device['context_name'] = $context_name;
            $valid = self::$func($device);
        }
        self::cleanupIpv6($device, $valid);
    }

    private function discoverIpv6_StandardOS(array $device): array
    {
        $valid = [];

        Log::debug('IPv6 -> discovering IP-MIB ...');
        $oids = SnmpQuery::enumStrings()->abortOnFailure()->walk(['IP-MIB::ipAddressIfIndex.ipv6', 'IP-MIB::ipAddressOrigin.ipv6', 'IP-MIB::ipAddressPrefix.ipv6'])->table(4);
        foreach ($oids['ipv6'] ?? [] as $address => $data) {
            try {
                $ifIndex = $data['IP-MIB::ipAddressIfIndex'];
                $ipv6_address = IPv6::fromHexString($address)->uncompressed();
                $ipv6_origin = $data['IP-MIB::ipAddressOrigin'] ?? null;
                preg_match('/(\d{1,3})]$/', $data['IP-MIB::ipAddressPrefix'], $prefix_match);
                $ipv6_prefixlen = intval($prefix_match[1]);

                if (! empty($ipv6_prefixlen) && ! empty($ipv6_origin)) {
                    $valid[self::processIpv6($device, $ifIndex, $ipv6_address, $ipv6_prefixlen, $ipv6_origin)] = true;
                }
            } catch (InvalidIpException $e) {
                Log::debug('Failed to decode ipv6: ' . $address);
            }
        }

        if (empty($oids) || empty($valid)) {
            Log::debug('IPv6 -> discovering IPV6-MIB ...');
            $oids = SnmpQuery::hideMib()->walk('IPV6-MIB::ipv6AddrPfxLength')->table(2);
            $oids = SnmpQuery::hideMib()->walk('IPV6-MIB::ipv6AddrType')->table(2, $oids);
            if (! empty($oids)) {
                foreach ($oids as $ifIndex => $data) {
                    $ipv6_address = key($data);
                    $ipv6_prefixlen = intval($data[$ipv6_address]['ipv6AddrPfxLength']);
                    $ipv6_origin = $data[$ipv6_address]['ipv6AddrType'] ?? null;
                    if (! empty($ipv6_address) && ! empty($ipv6_prefixlen) && ! empty($ipv6_origin)) {
                        $valid[self::processIpv6($device, $ifIndex, $ipv6_address, $ipv6_prefixlen, $ipv6_origin)] = true;
                    }
                }
            }
        }

        return $valid;
    }

    private function discoverIpv6_eltex_mes24xx(array $device): array
    {
        $valid = [];

        Log::debug('IPv6 -> discovering Eltex ...');
        $oids = SnmpQuery::hideMib()->walk('IP-MIB::ipAddressPrefixTable')->table(3);

        if (! empty($oids)) {
            foreach ($oids as $ifIndex => $indexData) {
                foreach ($indexData as $addrType => $addrData) {
                    if ($addrType == 'ipv6') {
                        try {
                            $ip = key($addrData);
                            $ipv6_address = IPv6::fromHexString($ip)->uncompressed();
                            $addrData = array_shift($addrData);
                            $prefixArr = $addrData['ipAddressPrefixOrigin'];
                            $ipv6_prefixlen = intval(key($prefixArr));
                            $ipv6_origin = $prefixArr[$ipv6_prefixlen] ?? null;

                            if (! empty($ipv6_prefixlen) && ! empty($ipv6_origin)) {
                                $valid[self::processIpv6($device, $ifIndex, $ipv6_address, $ipv6_prefixlen, $ipv6_origin)] = true;
                            }
                        } catch (InvalidIpException $e) {
                            Log::debug('IPv6 -> Failed to decode ipv6: ' . $ip);
                        }
                    }
                }
            }
        }

        return $valid;
    }

    private function discoverIpv6_eltex_mes23xx(array $device): array
    {
        $valid = [];

        Log::debug('IPv6 -> discovering Eltex ...');
        $oids = SnmpQuery::hideMib()->walk('IP-MIB::ipAddressIfIndex.ipv6')->table(2);
        $oids = SnmpQuery::hideMib()->walk('RADLAN-IPv6::rlIpAddressTable')->table(2, $oids);

        if (! empty($oids)) {
            foreach ($oids['ipv6'] as $ip => $addrData) {
                try {
                    $ifIndex = $addrData['ipAddressIfIndex'];
                    $ipv6_address = IPv6::fromHexString($ip)->uncompressed();
                    $ipv6_prefixlen = intval($addrData['rlIpAddressPrefixLength']);
                    $ipv6_origin = $addrData['rlIpAddressType'] ?? null;

                    if (! empty($ipv6_prefixlen) && ! empty($ipv6_origin)) {
                        $valid[self::processIpv6($device, $ifIndex, $ipv6_address, $ipv6_prefixlen, $ipv6_origin)] = true;
                    }
                } catch (InvalidIpException $e) {
                    Log::debug('IPv6 -> Failed to decode ipv6: ' . $ip);
                }
            }
        }

        return $valid;
    }

    private function discoverIpv6_eltex_mes21xx(array $device): array
    {
        $valid = [];

        Log::debug('IPv6 -> discovering Eltex ...');
        $oids = SnmpQuery::hideMib()->walk('IP-MIB::ipAddressIfIndex.ipv6')->table(2);
        $oids = SnmpQuery::hideMib()->walk('RADLAN-IPv6::rlIpAddressTable')->table(2, $oids);

        if (! empty($oids)) {
            foreach ($oids['ipv6'] as $ip => $addrData) {
                try {
                    $ifIndex = $addrData['ipAddressIfIndex'];
                    $ipv6_address = IPv6::fromHexString($ip)->uncompressed();
                    $ipv6_prefixlen = intval($addrData['rlIpAddressPrefixLength']);
                    $ipv6_origin = $addrData['rlIpAddressType'] ?? null;

                    if (! empty($ipv6_prefixlen) && ! empty($ipv6_origin)) {
                        $valid[self::processIpv6($device, $ifIndex, $ipv6_address, $ipv6_prefixlen, $ipv6_origin)] = true;
                    }
                } catch (InvalidIpException $e) {
                    Log::debug('IPv6 -> Failed to decode ipv6: ' . $ip);
                }
            }
        }

        return $valid;
    }

    private function discoverIpv6_jetstream(array $device): array
    {
        $valid = [];

        Log::debug('IPv6 -> discovering Jetstream ...');
        $oids = SnmpQuery::hideMib()->allowUnordered()->walk('TPLINK-IPV6ADDR-MIB::ipv6ParaConfigAddrTable')->table(4);

        if (! empty($oids)) {
            foreach ($oids as $vlanIf => $addrData) {
                $addrData = array_shift($addrData); // drop [ipv6]
                foreach ($addrData as $addrType => $perIpData) {
                    try {
                        $perIpData = array_shift($perIpData); // drop [decimal dotted ipv6 address]
                        $ip = $perIpData['ipv6ParaConfigAddress'];
                        $ifIndex = $perIpData['ipv6ParaConfigIfIndex'] ?? null;
                        $ipv6_address = IPv6::fromHexString($ip)->uncompressed() ?? null;
                        $ipv6_prefixlen = intval($perIpData['ipv6ParaConfigPrefixLength']);
                        $ipv6_origin = $perIpData['ipv6ParaConfigAddrType'] ?? null;

                        if (! empty($ipv6_prefixlen) && ! empty($ipv6_origin)) {
                            $valid[self::processIpv6($device, $ifIndex, $ipv6_address, $ipv6_prefixlen, $ipv6_origin)] = true;
                        }
                    } catch (InvalidIpException $e) {
                        Log::debug('IPv6 -> Failed to decode ipv6: ' . $ip);
                    }
                }
            }
        }

        return $valid;
    }

    private function processIpv6(array $device, int $ifIndex = 0, string $ipv6_address = '', int $ipv6_prefixlen = 0, string $ipv6_origin = ''): string
    {
        if (IPv6::isValid($ipv6_address, true)) {
            $ipv6 = new IPv6($ipv6_address);
            $ipv6_network = $ipv6->getNetwork($ipv6_prefixlen);
            $ipv6_compressed = $ipv6->compressed();
            Log::debug('IPv6 -> Processing ' . $ipv6_compressed . ' | ' . $ipv6_network);

            $port_id = Port::where([
                ['device_id', $device['device_id']],
                ['ifIndex', $ifIndex],
            ])->value('port_id');

            if (! empty($port_id) && $ipv6_prefixlen > '0' && $ipv6_prefixlen < '129' && ! empty($ipv6_origin)) {
                Log::debug('IPV6 -> Found port id ' . $port_id);

                $dbIpv6Net = Ipv6Network::updateOrCreate([
                    'ipv6_network' => $ipv6_network,
                ], [
                    'context_name' => $device['context_name'],
                ]);

                if (! $dbIpv6Net->wasRecentlyCreated && $dbIpv6Net->wasChanged()) {
                    Eventlog::log('IPv6 network ' . $ipv6_network . ' changed', $device['device_id'], 'ipv6', Severity::Warning);
                    echo 'Nu';
                }
                if ($dbIpv6Net->wasRecentlyCreated) {
                    Eventlog::log('IPv6 network ' . $ipv6_network . ' created', $device['device_id'], 'ipv6', Severity::Notice);
                    echo 'N+';
                }

                $ipv6_network_id = Ipv6Network::where('ipv6_network', $ipv6_network)->where('context_name', $device['context_name'])->value('ipv6_network_id');

                if ($ipv6_network_id) {
                    Log::debug('IPV6 -> Found network id ' . $ipv6_network_id);

                    $dbIpv6Addr = Ipv6Address::updateOrCreate([
                        'ipv6_address' => $ipv6_address,
                        'ipv6_prefixlen' => $ipv6_prefixlen,
                        'port_id' => $port_id,
                    ], [
                        'ipv6_compressed' => $ipv6_compressed,
                        'ipv6_origin' => $ipv6_origin,
                        'ipv6_network_id' => $ipv6_network_id,
                        'context_name' => $device['context_name'],
                    ]);

                    if (! $dbIpv6Addr->wasRecentlyCreated && $dbIpv6Addr->wasChanged()) {
                        Eventlog::log('IPv6 address ' . $ipv6_compressed . '/' . $ipv6_prefixlen . ' changed ', $device['device_id'], 'ipv6', Severity::Warning, $port_id);
                        echo 'Au';
                    }
                    if ($dbIpv6Addr->wasRecentlyCreated) {
                        Eventlog::log('IPv6 address ' . $ipv6_compressed . '/' . $ipv6_prefixlen . ' created ', $device['device_id'], 'ipv6', Severity::Notice, $port_id);
                        echo 'A+';
                    }

                    return $ipv6_address . '/' . $ipv6_prefixlen . '-' . $port_id;
                }
            }
        }

        return '0';
    }

    private function cleanupIpv6(array $device, array $valid): void
    {
        Log::debug('IPv6 -> Cleanup');

        $fromDb = Ipv6Address::where('ports.device_id', $device['device_id'])->orWhere('ports.device_id', null)
            ->select('ipv6_address_id', 'ipv6_address', 'ipv6_prefixlen', 'ipv6_network_id', 'ports.device_id', 'ports.port_id')
            ->leftJoin('ports', 'ipv6_addresses.port_id', '=', 'ports.port_id')
            ->get()->toArray();

        foreach ($fromDb as $row) {
            $full_address = $row['ipv6_address'] . '/' . $row['ipv6_prefixlen'] . '-' . $row['port_id'];
            if (empty($valid[$full_address])) {
                Ipv6Address::where('ipv6_address_id', $row['ipv6_address_id'])->delete();
                Eventlog::log('IPv6 address: ' . $row['ipv6_address'] . '/' . $row['ipv6_prefixlen'] . ' deleted', $device['device_id'], 'ipv6', Severity::Warning);
                echo 'A-';
                if (! Ipv6Address::where('ipv6_network_id', $row['ipv6_network_id'])->count()) {
                    Ipv6Network::where('ipv6_network_id', $row['ipv6_network_id'])->delete();
                    Eventlog::log('IPv6 network: ' . $row['ipv6_network'] . '/' . $row['ipv6_prefixlen'] . ' deleted', $device['device_id'], 'ipv6', Severity::Warning);
                    echo 'N-';
                }
            }
        }
    }
}
