<?php

/**
 * HostMac.php
 *
 * A MAC address of a host and when it was last seen
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

use Carbon\Carbon;

class HostMac
{
    /**
     * @param  bool  $stale  seen long before the host's newest MAC
     */
    public function __construct(
        public readonly string $hex,
        public readonly ?Carbon $seen,
        public readonly bool $stale,
    ) {
    }
}
