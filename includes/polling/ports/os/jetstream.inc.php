<?php
/**
 * jetstream.inc.php
 *
 * Jetstream OS port name/description rewrite
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
foreach ($port_stats as $key => $val) {

    //search for ': copper' string and replace to 'gigabitEthernet[nospace]1/0/x'
    if (strpos($port_stats[$key]['ifDescr'], ': copper') !== false) {
        $port_stats[$key]['ifDescr'] = str_replace([' ', ':', 'copper'], '', $port_stats[$key]['ifDescr']);
    }

    //search for ': copper' string and replace to 'gigabitEthernet[nospace]1/0/x'
    if (strpos($port_stats[$key]['ifName'], ': copper') !== false) {
        $port_stats[$key]['ifName'] = str_replace([' ', ':', 'copper'], '', $port_stats[$key]['ifName']);
    }

    //search for ': fiber' string and replace to 'FiberEthernet[nospace]1/0/x'. Capital 'F' !!!
    if (strpos($port_stats[$key]['ifDescr'], ': fiber') !== false) {
        $port_stats[$key]['ifDescr'] = str_replace([' ', ':', 'fiber'], '', $port_stats[$key]['ifDescr']);
        $port_stats[$key]['ifDescr'] = str_replace('gigabit', 'Fiber', $port_stats[$key]['ifDescr']);
    }

    //search for ': fiber' string and replace to 'FiberEthernet[nospace]1/0/x'. Capital 'F' !!!
    if (strpos($port_stats[$key]['ifName'], ': fiber') !== false) {
        $port_stats[$key]['ifName'] = str_replace([' ', ':', 'fiber'], '', $port_stats[$key]['ifName']);
        $port_stats[$key]['ifName'] = str_replace('gigabit', 'Fiber', $port_stats[$key]['ifName']);
    }
}
