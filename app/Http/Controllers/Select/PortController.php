<?php
/**
 * PortController.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Select;

use App\Models\Port;
use Illuminate\Contracts\Pagination\Paginator;

class PortController extends SelectController
{
    /**
     * Defines validation rules (will override base validation rules for select2 responses too)
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'field' => 'nullable|in:ifType',
            'device' => 'nullable|int',
        ];
    }

    /**
     * Defines search fields will be searched in order
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    protected function searchFields($request)
    {
        return (array)$request->get('field', ['ifAlias', 'ifName', 'ifDescr', 'devices.hostname', 'devices.sysName']);
    }

    /**
     * Defines the base query for this resource
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function baseQuery($request)
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = Port::hasAccess($request->user());

        if ($field = $request->get('field')) {
            $query->select($field)->distinct();
        } else {
            $query->with(['device' => function ($query) {
                $query->select('device_id', 'hostname', 'sysName');
            }])->leftJoin('devices', 'devices.device_id', 'ports.device_id')
                ->select('ports.device_id', 'port_id', 'ifAlias', 'ifName', 'ifDescr')
            ->groupBy(['ports.device_id', 'port_id', 'ifAlias', 'ifName', 'ifDescr']);
        }

        if ($device_id = $request->get('device')) {
            $query->where('ports.device_id', $device_id);
        }

        return $query;
    }

    /**
     * @param Paginator $paginator
     * @return \Illuminate\Http\JsonResponse
     */
    protected function formatResponse($paginator)
    {
        return response()->json([
            'results' => collect($paginator->items())->groupBy('ports.device_id')->map([$this, 'formatItem'])->values(),
            'pagination' => ['more' => $paginator->hasMorePages()]
        ]);
    }

    public function formatItem($data)
    {
        if ($data instanceof Port) {
            return parent::formatItem($data);
        }

        return [
            'text' => $data->first()->device->displayName(),
            'children' => $data->map(function ($port) {
                return [
                    'id' => $port->port_id,
                    'text' => $port->getLabel(),
                ];
            })->values(),
        ];
    }
}
