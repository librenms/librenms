<?php

/**
 * webpower-smart2.php discovery module for IPv4 Addresses
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
 * @copyright  2025 Peca Nesovanovic
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */
$oids = SnmpQuery::cache()->hideMib()->walk('IP-MIB::ipAddrTable')->table(1);

if (! empty($oids)) {
    unset($valid_v4);
    foreach ($oids as $key => $entry) {
        discover_process_ipv4($valid_v4, $device, $entry['ipAdEntIfIndex'], $entry['ipAdEntAddr'], $entry['ipAdEntNetMask'], $context_name);
    }
}
