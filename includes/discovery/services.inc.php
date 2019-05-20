<?php
/**
 * services.inc.php
 *
 * Creates the correct handler for the trap and then sends it the trap.
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 KanREN, Inc.
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

use LibreNMS\Config;

$oids = trim(snmp_walk($device, '.1.3.6.1.2.1.6.20.1.4', '-Osqn'));
foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    if ($data) {
        list($oid, $tcpstatus) = explode(' ', $data);
        $split_oid = explode('.', $oid);
        $tcp_port  = $split_oid[(count($split_oid) - 1)];
        $ipVersion = $split_oid[12];
        if ($ipVersion == 4) {
            $listenV4 = implode(".", [$split_oid[13], $split_oid[14], $split_oid[15], $split_oid[16]]);
            if ($listenV4 == "127.0.0.1") {
                continue;
            }
        } else {
            for ($i = 13, $arrayV6 = []; $i < 29; $i++) {
                $arrayV6[] = $split_oid[$i];
            }
            $listenV6 = implode($arrayV6);
            if ($listenV6 == "0000000000000001") {
                continue;
            }
        }
        $services[] = $tcp_port;
        if (($service = getservbyport($tcp_port, 'tcp')) && (1 === count(array_keys($services, $tcp_port)))) {
            discover_service($device, $service);
        }
    }
}
