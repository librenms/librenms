<?php
/*
 * DevicePortSearchController.php
 *
 * Serach for ports in a specific device
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
 * @copyright  2023 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.com.au>
 */

namespace App\Http\Controllers\Ajax;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DevicePortSearchController extends PortSearchController
{
    public function buildQuery(string $search, Request $request): Builder
    {
        $request->validate([
            'device_id' => 'integer',
        ]);
        $q = parent::buildQuery($search, $request);

        return $q->where('device_id', $request->device_id);
    }

    public function formatItem($port): array
    {
        $r = parent::formatItem($port);
        $r['device_id'] = $port->device_id;

        return $r;
    }
}
