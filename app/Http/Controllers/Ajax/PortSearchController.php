<?php
/*
 * PortSearchController.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Ajax;

use App\Models\Port;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use LibreNMS\Util\Color;
use LibreNMS\Util\Url;

class PortSearchController extends SearchController
{
    public function buildQuery(string $search, Request $request): Builder
    {
        return Port::hasAccess($request->user())
            ->with('device')
            ->where('deleted', 0)
            ->where(function (Builder $query) use ($request) {
                $search = $request->get('search');
                $like_search = "%$search%";

                return $query->orWhere('ifAlias', 'LIKE', $like_search)
                    ->orWhere('ifDescr', 'LIKE', $like_search)
                    ->orWhere('ifName', 'LIKE', $like_search)
                    ->orWhere('port_descr_descr', 'LIKE', $like_search)
                    ->orWhere('portName', 'LIKE', $like_search);
            })
            ->orderBy('ifDescr');
    }

    /**
     * @param  \App\Models\Port  $port
     * @return array
     */
    public function formatItem($port): array
    {
        $description = $port->getDescription();
        $label = $port->getLabel();

        if ($description !== $port->ifDescr && $label !== $port->ifDescr) {
            $description .= " ($port->ifDescr)";
        }

        return [
            'url'         => Url::portUrl($port),
            'name'        => $label,
            'description' => $description,
            'colours'     => Color::forPortStatus($port),
            'hostname'    => $port->device->displayName(),
            'port_id'     => $port->port_id,
        ];
    }
}
