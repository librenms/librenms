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
use Illuminate\Validation\Rule;
use LibreNMS\Util\Mac;
use LibreNMS\Util\Url;

class MacSearchController extends TableController
{
    protected function rules()
    {
        return [
            'address' => ['nullable', 'string'],
            'device_id' => ['nullable', 'integer'],
            'interface' => ['nullable', Rule::in('Vlan%', 'Loopback%')],
        ];
    }

    protected function sortFields($request)
    {
        return [
            'hostname' => 'device_hostname',
            'interface' => 'ifDescr',
            'description' => 'ifAlias',
            'address' => 'ifPhysAddress',
        ];
    }

    protected function baseQuery(Request $request)
    {
        return Port::query()
            ->hasAccess($request->user())
            ->with('device')
            ->when($request->get('device_id'), fn ($q, $id) => $q->where('device_id', $id))
            ->when($request->get('interface'), fn ($q, $i) => $q->where('ifDescr', 'LIKE', $i))
            ->when($request->get('address'), function ($q, $mac) {
                $cleanMac = str_replace([':', ' ', '-', '.', '0x'], '', $mac);

                return $q->where('ifPhysAddress', 'LIKE', "%$cleanMac%");
            })
            ->when($request->has('sort.hostname'), fn ($q) => $q->withAggregate('device', 'hostname'));
    }

    /**
     * @param  Port  $model
     * @return array
     */
    public function formatItem($model): array
    {
        $mac = Mac::parse($model->ifPhysAddress);

        return [
            'hostname' => Url::modernDeviceLink($model->device),
            'interface' => Url::portLink($model),
            'address' => $mac->readable(),
            'description' => $model->getLabel() == $model->ifAlias ? '' : $model->ifAlias,
            'mac_oui' => $mac->vendor(),
        ];
    }
}
