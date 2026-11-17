<?php

/**
 * LoadBalancerItem.php
 *
 * One entry of a load balancer section (virtual server, pool, pool member...), the same for every vendor
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

namespace App\Http\Controllers\Device\Tabs\LoadBalancer\Data;

use LibreNMS\Enum\Severity;

class LoadBalancerItem
{
    /**
     * @param  int  $id  the id graphs of this item use
     * @param  string|null  $address  ip:port the item listens on or forwards to
     * @param  string|null  $pool  name of the pool the item uses or belongs to
     * @param  int|null  $poolId  id of that pool in the pools section, when it is known
     * @param  array<string, string>  $columns  vendor specific values shown in lists and details, by column key (see LoadBalancerSource::columns())
     * @param  array<string, string>  $details  vendor specific values only shown in details, by title
     * @param  array<string, int|float>  $sortValues  values to sort columns by when their text does not sort, by column key
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly Severity $severity,
        public readonly string $status,
        public readonly ?string $address = null,
        public readonly ?string $pool = null,
        public readonly ?int $poolId = null,
        public readonly array $columns = [],
        public readonly array $details = [],
        public readonly array $sortValues = [],
    ) {
    }
}
