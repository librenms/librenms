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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Select;

use App\Models\Port;

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
        return (array) $request->get('field', ['ifAlias', 'ifName', 'ifDescr', 'devices.hostname', 'devices.sysName']);
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
        $query = Port::hasAccess($request->user())
            ->isNotDeleted()
            ->has('device')
            ->with(['device' => function ($query) {
                $query->select('device_id', 'hostname', 'sysName');
            }])
            ->select('ports.device_id', 'port_id', 'ifAlias', 'ifName', 'ifDescr')
            ->groupBy(['ports.device_id', 'port_id', 'ifAlias', 'ifName', 'ifDescr']);

        if ($request->get('term')) {
            // join with devices for searches
            $query->leftJoin('devices', 'devices.device_id', 'ports.device_id');
        }

        if ($device_id = $request->get('device')) {
            $query->where('ports.device_id', $device_id);
        }

        return $query;
    }

    public function formatItem($port)
    {
        /** @var Port $port */
        $label = $port->getShortLabel();
        $description = ($label == $port->ifAlias ? '' : ' - ' . $port->ifAlias);

        return [
            'id' => $port->port_id,
            'text' => $label . ' - ' . $port->device->shortDisplayName() . $description,
        ];
    }
}
