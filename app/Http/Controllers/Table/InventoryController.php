<?php
/**
 * InventoryController.php
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
 * @copyright  2023 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Table;

use App\Models\EntPhysical;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use LibreNMS\Util\Url;

class InventoryController extends TableController
{
    public function rules()
    {
        return [
            'device' => 'nullable|int',
            'descr' => 'nullable|string',
            'model' => 'nullable|string',
            'serial' => 'nullable|string',
        ];
    }

    protected function filterFields($request)
    {
        return [
            'device_id' => 'device',
        ];
    }

    protected function searchFields($request)
    {
        return ['entPhysicalDescr', 'entPhysicalModelName', 'entPhysicalSerialNum'];
    }

    protected function sortFields($request)
    {
        return [
            'device' => 'device_id',
            'name' => 'entPhysicalName',
            'descr' => 'entPhysicalDescr',
            'model' => 'entPhysicalModelName',
            'serial' => 'entPhysicalSerialNum',
        ];
    }

    protected function baseQuery($request)
    {
        $query = EntPhysical::hasAccess($request->user())
            ->with('device')
            ->select(['entPhysical_id', 'device_id', 'entPhysicalDescr', 'entPhysicalName', 'entPhysicalModelName', 'entPhysicalSerialNum']);

        // apply specific field filters
        $this->search($request->get('descr'), $query, ['entPhysicalDescr']);
        $this->search($request->get('model'), $query, ['entPhysicalModelName']);
        $this->search($request->get('serial'), $query, ['entPhysicalSerialNum']);

        return $query;
    }

    /**
     * @param  EntPhysical  $entPhysical
     * @return array|Model|Collection
     */
    public function formatItem($entPhysical)
    {
        return [
            'device' => Url::deviceLink($entPhysical->device),
            'descr' => htmlspecialchars($entPhysical->entPhysicalDescr),
            'name' => htmlspecialchars($entPhysical->entPhysicalName),
            'model' => htmlspecialchars($entPhysical->entPhysicalModelName),
            'serial' => htmlspecialchars($entPhysical->entPhysicalSerialNum),
        ];
    }
}
