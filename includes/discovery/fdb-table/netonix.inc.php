<?php

/**
 * netonix.inc.php
 *
 * Discover FDB data for Netonix switches
 *
 * The agent indexes BRIDGE-MIB::dot1dTpFdbTable by row number instead of MAC address,
 * so the MAC has to be read from dot1dTpFdbAddress and joined to dot1dTpFdbPort by row.
 * It does not implement Q-BRIDGE-MIB::dot1qTpFdbTable, so there is no VLAN information.
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

use Illuminate\Support\Facades\Log;
use LibreNMS\Util\Mac;

$fdbRows = SnmpQuery::hideMib()->walk(['BRIDGE-MIB::dot1dTpFdbAddress', 'BRIDGE-MIB::dot1dTpFdbPort'])->valuesByIndex();
$basePortIfIndex = SnmpQuery::hideMib()->walk('BRIDGE-MIB::dot1dBasePortIfIndex')->pluck();

foreach ($fdbRows as $row) {
    $dot1dBasePort = $row['dot1dTpFdbPort'] ?? 0;
    if (empty($row['dot1dTpFdbAddress']) || $dot1dBasePort == 0) {
        continue; // incomplete row or the switch's own address
    }

    $mac_address = Mac::parse($row['dot1dTpFdbAddress'])->hex();
    if (strlen($mac_address) != 12) {
        Log::debug("MAC address padding failed for {$row['dot1dTpFdbAddress']}\n");
        continue;
    }

    $port_id = PortCache::getIdFromIfIndex($basePortIfIndex[$dot1dBasePort] ?? $dot1dBasePort);
    $insert[0][$mac_address]['port_id'] = $port_id;
    Log::debug("mac $mac_address port ($dot1dBasePort) $port_id\n");
}

unset($fdbRows, $basePortIfIndex);
