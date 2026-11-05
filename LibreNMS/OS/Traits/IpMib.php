<?php

/**
 * IpMib.php
 *
 * IP-MIB walks shared by the IPv4 and IPv6 address modules.
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
 */

namespace LibreNMS\OS\Traits;

use LibreNMS\Data\Source\Snmp\SnmpResponse;
use SnmpQuery;

trait IpMib
{
    /**
     * Walk ipAddressTable once per context for both address modules.
     *
     * The cached response is reused unless the agent did not answer, so a timeout in
     * whichever module runs first cannot hide the other module's addresses.
     */
    public function walkIpAddressTable(string $context_name): SnmpResponse
    {
        $query = fn () => SnmpQuery::context($context_name)->allowUnordered()->enumStrings();
        $response = $query()->cache()->walk('IP-MIB::ipAddressTable');

        return $response->hasConnectionError() ? $query()->walk('IP-MIB::ipAddressTable') : $response;
    }
}
