<?php
/**
 * IPv6 address discovery file for eltex-mes24xx OS
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

//IP-MIB
$oids = SnmpQuery::hideMib()->walk('IP-MIB::ipAddressPrefixTable')->table(3);

if (! empty($oids)) {
    d_echo('Eltex 24xx IPv6 discovering ...');

    foreach ($oids as $index => $indexData) {
        foreach ($indexData as $addrType => $addrData) {
            if ($addrType != 'ipv6') {
                continue;
            }
            $v6addr = normalize_snmp_ip_address(key($addrData));
            $addrData = array_shift($addrData);
            $prefixArr = $addrData['ipAddressPrefixOrigin'];
            $prefixLen = key($prefixArr);
            $prefixType = $prefixArr[$prefixLen];
            discover_process_ipv6($valid, $index, $v6addr, $prefixLen, 'manual', $device['context_name']);
        }
    }
}
