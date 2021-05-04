<?php
/*
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers;

use App\Models\Port;

class PortController extends Controller
{
    public function update(\Illuminate\Http\Request $request, Port $port)
    {
        $this->validate($request, [
            'groups.*' => 'int',
        ]);

        $updated = false;
        $message = '';

        if ($request->has('groups')) {
            $changes = $port->groups()->sync($request->get('groups'));
            $groups_updated = array_sum(array_map(function ($group_ids) {
                return count($group_ids);
            }, $changes));

            if ($groups_updated > 0) {
                $message .= trans('port.groups.updated', ['port' => $port->getLabel()]);
                $updated = true;
            }
        }

        return $updated
            ? response(['message' => $message])
            : response(['message' => trans('port.groups.none', ['port' => $port->getLabel()])], 400);
    }
}
