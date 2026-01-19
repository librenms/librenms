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
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Util\IP;
use LibreNMS\Util\Url;

/**
 * @template TModel of Model
 */
abstract class AddressSearchController extends TableController
{
    /** @var string|Expression (string or DB::raw) */
    protected mixed $sortField = ''; // set for sort
    protected string $searchField = '';
    protected string $additionalSearchField = '';
    protected string $cidrField = ''; // set for display

    protected function sortFields($request)
    {
        return [
            'hostname' => 'device_via_port_hostname',
            'interface' => 'port_ifname',
            'description' => 'port_description',
            'address' => $this->sortField,
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
     * @param  TModel&object{port: Port|null}  $model
     * @return string[]
     *
     * @throws InvalidIpException
     */
    public function formatItem($model): array
    {
        $port = $model->port;

        return [
            'hostname' => Url::modernDeviceLink($port?->device),
            'interface' => Url::portLink($port),
            'address' => IP::parse($model->{$this->searchField}, true)->compressed() . '/' . $model->{$this->cidrField},
            'description' => $port->getLabel() == $port->ifAlias ? '' : $port->ifAlias,
        ];
    }

    protected function applyBaseSearchQuery(Builder $builder, Request $request): Builder
    {
        return $builder
            ->when($request->get('address'), function ($q, $address): void {
                if (str_contains($address, '/')) {
                    [$address, $cidr] = explode('/', $address, 2);
                }

                $q->where(fn ($q) => $q->where($this->searchField, 'LIKE', "%$address%")->when($this->additionalSearchField, fn ($q, $f) => $q->orWhere($f, 'LIKE', "%$address%")));

                if (isset($cidr)) {
                    $q->where($this->cidrField, $cidr);
                }
            })
            ->when($request->get('device_id'), fn ($q, $id) => $q->whereHas('port', fn ($pq) => $pq->where('device_id', $id)))
            ->when($request->get('interface'), fn ($q, $i) => $q->whereHas('port', fn ($pq) => $pq->where('ifDescr', 'LIKE', $i)))
            ->when($request->has('sort.hostname'), fn ($q) => $q->withAggregate('deviceViaPort', 'hostname'))
            ->when($request->has('sort.interface'), fn ($q) => $q->withAggregate('port', 'ifName'))
            ->when($request->has('sort.description'), function ($q) use ($builder): void {
                $q->select($builder->getModel()->getTable() . '.*')->selectSub(function ($sub) use ($builder): void {
                    $sub->selectRaw('IF(ifAlias = ifName || ifAlias = ifDescr, "", ifAlias)')
                        ->from('ports')
                        ->whereColumn('ports.port_id', $builder->qualifyColumn('port_id'))
                        ->limit(1);
                }, 'port_description');
            });
    }
}
