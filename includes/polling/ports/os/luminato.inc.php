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

// add IF-MIB::ifSpeed if missing
if (! array_key_exists('ifSpeed', Arr::first($port_stats))) {
    SnmpQuery::hideMib()->walk('IF-MIB::ifSpeed')->table(2, $port_stats);
}

foreach ($port_stats as $key => $data) {
    // emulate ifOperStatus if missing
    if (empty($data['ifOperStatus'])) {
        $port_stats[$key]['ifOperStatus'] = $data['ifConnectorPresent'] ? 'up' : 'down';
    }

    // ifHighSpeed is always broken and ver >= 20 ifSpeed is actually ifHighSpeed
    $port_stats[$key]['ifHighSpeed'] = ($ver < 20 ? $data['ifSpeed'] / 1000000 : $data['ifSpeed']);
}
