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
 *
 * @copyright  2021 Peca Nesovanovic
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

//IP-MIB
$oids = SnmpQuery::walk('IP-MIB::ipAddressIfIndex.ipv6')->table(2);
//merge Radlan-IPv6 with IP-MIB
$oidm = SnmpQuery::walk('RADLAN-IPv6::rlIpAddressTable')->table(2, $oids);

if ($oidm) {
    d_echo('Eltex IPv6: discovering ...');

    foreach ($oidm['ipv6'] as $ip => $iparray) {
        d_echo('Eltex IPv6: processing ' . $ip);

        $index = $iparray['IP-MIB::ipAddressIfIndex'];
        $prefixlen = $iparray['RADLAN-IPv6::rlIpAddressPrefixLength'];
        $type = $iparray['RADLAN-IPv6::rlIpAddressType'];
        $v6addr = normalize_snmp_ip_address($ip); //convert from xx:xx:xx:xx to xxxx:xxxx

        if ($type == 1 && $index && $prefixlen) {
            discover_process_ipv6($valid, $index, $v6addr, $prefixlen, 'manual', $device['context_name']);
        }
    }
}
