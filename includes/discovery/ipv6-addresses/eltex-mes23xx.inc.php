<?php
/**
 * mes23xx.inc.php
 *
 * IPv6 address discovery file for eltex-mes23xx OS
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
 * @copyright  2021 Peca Nesovanovic
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

//standard MIB
$oids = SnmpQuery::walk('IP-MIB::ipAddressIfIndex.ipv6')->table(2);
//Radlan MIB
$oidr = SnmpQuery::walk('RADLAN-IPv6::rlIpAddressTable')->table(2);

if ($oids && $oidr) {
    d_echo ('Eltex IPv6: discovering ...');

    foreach ($oids['ipv6'] as $ip => $iparray) {
        d_echo ('Eltex IPv6: processing ' . $ip);

        $index = $iparray['IP-MIB::ipAddressIfIndex'];
        $prefixlen = $oidr['ipv6'][$ip]['RADLAN-IPv6::rlIpAddressPrefixLength'];
        $split = str_split(str_replace(':', '', strtolower($ip)), 4); //convert colon delimited hex IPv6 address to array, every forth char
        $v6addr = implode(':', $split); //assemble array in 0000:1111 format

        if ($oidr['ipv6'][$ip]['RADLAN-IPv6::rlIpAddressType'] == 1 && $index && $prefixlen) {
            discover_process_ipv6($valid, $index, $v6addr, $prefixlen, 'manual', $device['context_name']);
        }
    }
}
