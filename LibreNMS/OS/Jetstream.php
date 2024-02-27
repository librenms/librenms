<?php
/*
 * Jetstream.php
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
 * @copyright  2024 Peca Nesovanovic
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Interfaces\Discovery\Ipv6Discovery;
use LibreNMS\OS;
use LibreNMS\Util\IPv6;
use Log;
use SnmpQuery;

class Jetstream extends OS implements
    Ipv6Discovery
{
    public function discoverIpv6(): array
    {
        $retData = [];

        Log::debug('IPv6 -> discovering Jetstream ...');
        $oids = SnmpQuery::hideMib()->allowUnordered()->walk('TPLINK-IPV6ADDR-MIB::ipv6ParaConfigAddrTable')->table(3);

        if (! empty($oids)) {
            $ipData = [];
            foreach ($oids as $indexKey => $addrData) {
                $addrData = array_shift($addrData); // drop [ipv6]
                foreach ($addrData as $addrType => $perTypeData) {
                    foreach ($perTypeData['ipv6ParaConfigIfIndex'] as $dotDec => $value) {
                        $value = is_array($value) ? array_shift($value) : $value; // IPv4-compatible IPv6 addresses extra array
                        $ipData[$dotDec]['ifindex'] = $value;
                    }
                    foreach ($perTypeData['ipv6ParaConfigAddress'] as $dotDec => $value) {
                        $value = is_array($value) ? array_shift($value) : $value; // IPv4-compatible IPv6 addresses extra array
                        $ipData[$dotDec]['hexaddress'] = $value;
                    }
                    foreach ($perTypeData['ipv6ParaConfigPrefixLength'] as $dotDec => $value) {
                        $value = is_array($value) ? array_shift($value) : $value; // IPv4-compatible IPv6 addresses extra array
                        $ipData[$dotDec]['prefixlen'] = $value;
                    }
                    foreach ($perTypeData['ipv6ParaConfigSourceType'] as $dotDec => $value) {
                        $value = is_array($value) ? array_shift($value) : $value; // IPv4-compatible IPv6 addresses extra array
                        $ipData[$dotDec]['origin'] = $value;
                    }
                } // typeData

                foreach ($ipData as $key => $finalData) {
                    try {
                        $ifIndex = intval($finalData['ifindex']);
                        $address = IPv6::fromHexString($finalData['hexaddress'])->compressed() ?? null;
                        $prefixlen = intval($finalData['prefixlen']);
                        $origin = self::translateAddrType(intval($finalData['origin']));

                        if (! empty($prefixlen) && ! empty($origin)) {
                            $retData[] = ['ifIndex' => $ifIndex, 'address' => $address, 'prefixlen' => $prefixlen, 'origin' => $origin];
                        }
                    } catch (InvalidIpException $e) {
                        Log::debug('IPv6 -> Failed to decode ipv6: ' . $finalData['hexaddress']);
                    }
                } // finalData
            } // addrsData
        } // oids

        return $retData;
    }

    private function translateAddrType(int $type): string
    {
        $addrTypes = [
            1 => 'stateful',
            2 => 'stateless',
            3 => 'assignedLinklocalIp',
            4 => 'autoIp',
            5 => 'dhcpv6',
            6 => 'negotiate',
        ];

        return $addrTypes[$type] ?? 'unknown';
    }
}
