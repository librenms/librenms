<?php
/**
 * SearchController.php
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use LibreNMS\Util\Url;

/**
 * @template TModel of Model
 */
abstract class SearchController extends TableController
{
    protected string $addressField = ''; // set for sort

    protected function sortFields($request)
    {
        return [
            'hostname' => 'device_hostname',
            'port' => 'port_ifDescr',
            'description' => 'port_ifAlias',
            'address' => $this->addressField,
        ];
    }

    protected function rules()
    {
        return [
            'address' => ['nullable', 'string'],
            'device_id' => ['nullable', 'integer'],
            'interface' => ['nullable', Rule::in('Vlan%', 'Loopback%')],
        ];
    }

    /**
     * @param  TModel  $model
     * @return string[]
     */
    public function formatItem($model): array
    {
        /** @var Port $port */
        $port = $model instanceof Port ? $model : $model->port;
        $descr = $port->getLabel() == $port->ifAlias ? '' : $port->ifAlias;

        $fields = [
            'hostname' => Url::modernDeviceLink($port?->device),
            'interface' => Url::portLink($port),
            'address' => $this->getAddress($model),
            'description' => $descr,
        ];

        $oui = $this->getOui($model);
        if ($oui !== null) {
            $fields['mac_oui'] = $oui;
        }

        return $fields;
    }

    /**
     * @param  TModel  $model
     * @return string
     */
    abstract protected function getAddress($model): string;

    /**
     * @param  TModel  $model
     * @return ?string
     */
    protected function getOui($model): ?string
    {
        return null;
    }

    protected function applyBaseSearchQuery(Builder $builder, Request $request): Builder
    {
        $tableIsPorts = $builder->getModel()->getTable() == 'ports';

        if ($tableIsPorts) {
            $builder->when($request->get('device_id'), fn($q, $device_id) => $q->where('device_id', $device_id));
            $builder->when($request->get('interface'), fn($q, $interface) => $q->where('ifDescr', 'LIKE', $interface));
        } else {
            $builder->when($request->get('device_id'), fn($q, $device_id) => $q->whereHas('port', fn ($q) => $q->where('device_id', $device_id)));
            $builder->when($request->get('interface'), fn($q, $interface) => $q->whereHas('port', fn ($q) => $q->where('ifDescr', 'LIKE', $interface)));
        }

        if ($request->has('sort.hostname')) {
            $builder->withAggregate('device','hostname');
        }

        if ($request->has('sort.interface')) {
            if (! $tableIsPorts) {
                $builder->withAggregate('port','ifDescr');
            }
        }

        if ($request->has('sort.description')) {
            if (! $tableIsPorts) {
                $builder->withAggregate('port','ifAlias');
            }
        }

        return $builder;
    }
}
