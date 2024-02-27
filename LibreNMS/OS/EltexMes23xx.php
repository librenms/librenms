<?php
/**
 * EltexMes23xx.php
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
 * @copyright  2022 PipoCanaja
 * @author     PipoCanaja
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Interfaces\Discovery\Ipv6Discovery;
use LibreNMS\OS;
use LibreNMS\Util\IPv6;
use Log;
use SnmpQuery;

class EltexMes23xx extends OS implements
    Ipv6Discovery
{
    /**
     * Specific HexToString for Eltex
     */
    public function normData(string $par = ''): string
    {
        $tmp = str_replace([':', ' '], '', trim(strtoupper($par)));
        $ret = preg_match('/^[0-9A-F]+$/', $tmp) ? hex2str($tmp) : $par; //if string is pure hex, convert to ascii

        return $ret;
    }

    public function discoverIpv6(): array
    {
        $retData = [];

        Log::debug('IPv6 -> discovering Eltex ...');
        $oids = SnmpQuery::hideMib()->walk('IP-MIB::ipAddressIfIndex.ipv6')->table(2);
        $oids = SnmpQuery::hideMib()->walk('RADLAN-IPv6::rlIpAddressTable')->table(2, $oids);

        if (! empty($oids)) {
            foreach ($oids['ipv6'] as $ip => $addrData) {
                try {
                    $ifIndex = $addrData['ipAddressIfIndex'];
                    $address = IPv6::fromHexString($ip)->compressed();
                    $prefixlen = intval($addrData['rlIpAddressPrefixLength']);
                    $origin = $addrData['rlIpAddressType'] ?? null;

                    if (! empty($prefixlen) && ! empty($origin)) {
                        $retData[] = ['ifIndex' => $ifIndex, 'address' => $address, 'prefixlen' => $prefixlen, 'origin' => $origin];
                    }
                } catch (InvalidIpException $e) {
                    Log::debug('IPv6 -> Failed to decode ipv6: ' . $ip);
                }
            }
        }

        return $retData;
    }
}
