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
use App\Models\Ipv6Address;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\Ipv6Discovery;
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
        $valid = [];

        foreach ($os->getDevice()->getVrfContexts() as $context_name) {
            if ($os instanceof Ipv6Discovery) {
                $data = $os->discoverIpv6();
            } else {
                $data = self::discoverIpv6();
            }

            if (! empty($data)) {
                Ipv6Address::processIpv6($os->getDeviceId(), $data);
            }
        }
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
        $device->ipv6()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device)
    {
        $fromDb = $device->ipv6()
        ->orderBy('ipv6_address')
        ->select(['ipv6_addresses.*', 'ports.ifIndex'])
        ->get();

        return [
            'ipv6_data' => $fromDb->map->makeHidden(['ipv6_address_id', 'device_id', 'ipv6_address', 'port_id', 'laravel_through_key']),
        ];
    }

    private function discoverIpv6(): array
    {
        $retData = [];

        Log::debug('IPv6 -> discovering IP-MIB ...');
        $oids = SnmpQuery::enumStrings()->abortOnFailure()->walk(['IP-MIB::ipAddressIfIndex.ipv6', 'IP-MIB::ipAddressOrigin.ipv6', 'IP-MIB::ipAddressPrefix.ipv6'])->table(4);
        foreach ($oids['ipv6'] ?? [] as $address => $data) {
            try {
                $ifIndex = $data['IP-MIB::ipAddressIfIndex'];
                $address = IPv6::fromHexString($address)->compressed();
                $origin = $data['IP-MIB::ipAddressOrigin'] ?? null;
                preg_match('/(\d{1,3})]$/', $data['IP-MIB::ipAddressPrefix'], $prefix_match);
                $prefixlen = intval($prefix_match[1]);

                if (! empty($prefixlen) && ! empty($origin)) {
                    $retData[] = ['ifIndex' => $ifIndex, 'address' => $address, 'prefixlen' => $prefixlen, 'origin' => $origin];
                }
            } catch (InvalidIpException $e) {
                Log::debug('Failed to decode ipv6: ' . $address);
            }
        }

        if (empty($oids) || empty($retData)) {
            Log::debug('IPv6 -> discovering IPV6-MIB ...');
            $oids = SnmpQuery::hideMib()->walk('IPV6-MIB::ipv6AddrPfxLength')->table(2);
            $oids = SnmpQuery::hideMib()->walk('IPV6-MIB::ipv6AddrType')->table(2, $oids);
            if (! empty($oids)) {
                foreach ($oids as $ifIndex => $entryData) {
                    foreach ($entryData as $address => $addrData) {
                        $prefixlen = intval($addrData['ipv6AddrPfxLength']);
                        $origin = self::translateAddrType(intval($addrData['ipv6AddrType']));
                        if (! empty($prefixlen) && ! empty($origin)) {
                            $retData[] = ['ifIndex' => $ifIndex, 'address' => $address, 'prefixlen' => $prefixlen, 'origin' => $origin];
                        }
                    }
                }
            }
        }

        return $retData;
    }

    private function translateAddrType(int $type): string
    {
        $addrTypes = [
            1 => 'stateless',
            2 => 'stateful',
        ];

        return $addrTypes[$type] ?? 'unknown';
    }
}
