<?php

/**
 * Ipv4SearchController.php
 *
 * -Description-
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

namespace App\Http\Controllers\Table;

use App\Models\Ipv4Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @extends AddressSearchController<Ipv4Address>
 */
class Ipv4AddressSearchController extends AddressSearchController
{
    public function __construct()
    {
        $this->sortField = DB::raw('INET_ATON(ipv4_address)');
        $this->searchField = 'ipv4_address';
        $this->cidrField = 'ipv4_prefixlen';
    }

    /**
     * @inheritDoc
     */
    protected function baseQuery(Request $request)
    {
        return $this->applyBaseSearchQuery(
            Ipv4Address::query()->hasAccess($request->user())->with(['port', 'port.device']),
            $request
        );
    }
}
