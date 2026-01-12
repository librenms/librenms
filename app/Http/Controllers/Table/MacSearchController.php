<?php
/**
 * MacSearchController.php
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

use App\Models\Port;
use Illuminate\Http\Request;
use LibreNMS\Util\Mac;

/**
 * @extends SearchController<Port>
 */
class MacSearchController extends SearchController
{
    protected string $addressField = 'ifPhysAddress';

    /**
     * @inheritDoc
     */
    protected function baseQuery(Request $request)
    {
        $query = Port::query()->hasAccess($request->user())->with('device');

        $this->applyBaseSearchQuery($query, $request);

        return $query;
    }

    /**
     * @inheritDoc
     */
    protected function getAddress($model): string
    {
        return Mac::parse($model->ifPhysAddress)->readable();
    }

    /**
     * @inheritDoc
     */
    protected function getOui($model): ?string
    {
        return Mac::parse($model->ifPhysAddress)->vendor();
    }
}
