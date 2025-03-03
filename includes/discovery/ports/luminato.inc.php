<?php
/**
 * luminato.inc.php
 *
 * LibreNMS discovery module for Teleste Luminato. Modify ifOperStatus
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
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */
$ver = intval($device['version']);
d_echo('PORTS: Luminato v' . $ver);

if ($ver >= 20) {
    $ifmib = SnmpQuery::walk('IF-MIB::ifConnectorPresent')->table(2);
    foreach ($port_stats as $key => $data) {
        $port_stats[$key]['ifOperStatus'] = $ifmib[$key]['IF-MIB::ifConnectorPresent'] ? 'up' : 'down';
    }
}
