<?php
/**
 * jetstream.inc.php
 *
 * IPv6 address discovery file for Jetstream OS
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
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */
$oids = snmp_walk($device, 'ipv6ParaConfigAddrTable', ['-OsQ', '-Ln', '-Cc'], 'TPLINK-IPV6ADDR-MIB');
$oids = trim($oids);
$v6data = [];
foreach (explode("\n", $oids) as $data) {
    $param = explode('.', $data)[0];
    $index = explode('.', $data)[1]; //iFindex
    $atype = explode('.', $data)[3];
    $erase = $param . '.' . $index . '.ipv6.' . $atype . '.'; //this will be erased from line
    $link = trim(explode('=', str_replace($erase, '', $data))[0]);
    $value = trim(explode('=', $data)[1]);
    if ($param == 'ipv6ParaConfigAddress') {
        $v6data[$link]['index'] = $index;
        $split = str_split(str_replace(' ', '', strtolower($value)), 4); //convert space delimited hex IPv6 address to array, every forth char
        $v6data[$link]['addr'] = implode(':', $split); //assemble array in 0000:1111 format
        $v6data[$link]['origin'] = ($atype == 'autoIp' ? 'linklayer' : 'manual'); //address type
    }
    if ($param == 'ipv6ParaConfigPrefixLength') {
        $prefixlen = intval($value);
        discover_process_ipv6($valid, $v6data[$link]['index'], $v6data[$link]['addr'], $prefixlen, $v6data[$link]['origin'], $device['context_name']);
    }
} //end foreach
