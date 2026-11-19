<?php

/**
 * Gateway.php
 *
 * A gateway of a host: a default route's next hop, or a router on its subnet
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

namespace App\Http\Controllers\Search\Data;

use App\Models\Port;
use LibreNMS\Util\IP;

class Gateway
{
    /**
     * @param  Port|null  $port  the interface with this IP, when it is monitored
     * @param  int  $devices  how many devices have a default route through it, 0 for a router found on the subnet
     */
    public function __construct(
        public readonly IP $ip,
        public readonly ?Port $port,
        public readonly int $devices,
    ) {
    }
}
