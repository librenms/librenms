<?php
/**
 * EltexMes24xx.php
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

namespace LibreNMS\OS;

use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Interfaces\Discovery\Ipv6Discovery;
use LibreNMS\OS;
use LibreNMS\Util\IPv6;
use Log;
use SnmpQuery;

class EltexMes24xx extends OS implements
    Ipv6Discovery
{
    public function discoverIpv6(): array
    {
        $retData = [];

        Log::debug('IPv6 -> discovering Eltex ...');
        $oids = SnmpQuery::hideMib()->walk('IP-MIB::ipAddressPrefixTable')->table(4);
        if (! empty($oids)) {
            foreach ($oids as $ifIndex => $indexData) {
                foreach ($indexData as $addrType => $addrData) {
                    if ($addrType == 'ipv6') {
                        try {
                            $ip = key($addrData);
                            $address = IPv6::fromHexString($ip)->compressed();
                            $addrData = array_shift($addrData);
                            $prefixlen = key($addrData);
                            $addrData = array_shift($addrData);
                            $origin = self::translateAddrType(intval($addrData['ipAddressPrefixOrigin']));
                            if (! empty($prefixlen) && ! empty($origin)) {
                                $retData[] = ['ifIndex' => $ifIndex, 'address' => $address, 'prefixlen' => $prefixlen, 'origin' => $origin];
                            }
                        } catch (InvalidIpException $e) {
                            Log::debug('IPv6 -> Failed to decode ipv6: ' . $ip);
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
            1 => 'other',
            2 => 'stateful',
            3 => 'wellknown',
            4 => 'dhcp',
            5 => 'stateless',
        ];

        return $addrTypes[$type] ?? 'unknown';
    }
}
