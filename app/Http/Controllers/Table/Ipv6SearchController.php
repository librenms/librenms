<?php
/**
 * Ipv6SearchController.php
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

use App\Models\Ipv6Address;
use Illuminate\Http\Request;
use LibreNMS\Util\IP;

/**
 * @extends SearchController<Ipv6Address>
 */
class Ipv6SearchController extends SearchController
{
    protected string $addressField = 'ipv6_compressed';

    /**
     * @inheritDoc
     */
    protected function baseQuery(Request $request)
    {
        $query = Ipv6Address::query()->hasAccess($request->user())->with(['port', 'device']);

        $address = $request->get('address');
        if ($address) {
            if (str_contains($request->get('address'), '/')) {
                [$address, $cidr] = explode('/', $address, 2);
                $query->where('ipv6_prefixlen', $cidr);
            }

            $query->where(fn($q) => $q->where('ipv6_address', 'LIKE', "%$address%")->orWhere('ipv6_compressed', 'LIKE', "%$address%"));
        }


        $this->applyBaseSearchQuery($query, $request);

        return $query;
    }

    /**
     * @inheritDoc
     */
    protected function getAddress($model): string
    {
        return (string) IP::parse($model->ipv6_address, true) . '/' . $model->ipv6_prefixlen;
    }
}
