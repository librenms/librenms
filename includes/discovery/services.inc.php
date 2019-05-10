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
 * Autodiscovers TCP services on devices in the 'server' device type.
 * To use set in config.php:
 * [discover_services] = 'true';
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 KanREN, Inc.
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

use LibreNMS\Config;

if (Config::get('discover_services')) {
    // Services
    if ($device['type'] == 'server') {
        $oidV4 = trim(snmp_walk($device, '.1.3.6.1.2.1.6.20.1.4.1.4.0.0.0.0', '-Osqn'));
        $oidV6 = trim(snmp_walk($device, '.1.3.6.1.2.1.6.20.1.4.2.16.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0', '-Osqn'));
        $oids = $oidV4 . $oidV6;
        foreach (explode("\n", $oids) as $data) {
            $data = trim($data);
            if ($data) {
                list($oid, $tcpstatus) = explode(' ', $data);
                $split_oid = explode('.', $oid);
                $tcp_port  = $split_oid[(count($split_oid) - 1)];
                if (getservbyport($tcp_port, 'tcp')) {
                    $services[] = getservbyport($tcp_port, 'tcp');
                }
            }
        }
        foreach ($services as $service) {
            discover_service($device, $service);
        }
    }
    echo "\n";
}
