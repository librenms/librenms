<?php
/**
 * luminato.inc.php
 *
 * LibreNMS poller module for Teleste Luminato. Modify: ifSpeed ifHighSpeed ifOperStatus
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
 * @copyrigh   2021 Peca Nesovanovic
 *
 * @author     peca.nesovanovic@sattrakt.com
 */
$ver = intval($device['version']);
d_echo('PORTS: Luminato v' . $ver);

$rfcmib = SnmpQuery::walk('RFC1213-MIB::ifSpeed')->table(2);

foreach ($port_stats as $key => $data) {
    $speed = $rfcmib[$key]['RFC1213-MIB::ifSpeed'];

    if ($ver >= 20) {
        $port_stats[$key]['ifOperStatus'] = $data['ifConnectorPresent'] ? 'up' : 'down';
    } else {
        $speed = $speed / 1000000;
    }

    $port_stats[$key]['ifHighSpeed'] = $speed;
    $port_stats[$key]['ifSpeed'] = 0;
}
