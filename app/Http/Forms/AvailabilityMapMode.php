<?php
/**
 * AvailabilityMapMode.php
 *
 * Sets the preferred view for the full page availability map.
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

namespace App\Http\Forms;

use App\Models\UserPref;
use Illuminate\Http\Request;

class AvailabilityMapMode extends BaseForm
{
    protected $validation_rules = [
        'map_view' => 'integer',
        'group_view' => 'integer',
    ];

    /**
     * @param Request $request
     * @return array
     */
    public function handleRequest(Request $request)
    {

        if ($request->has('map_view')) {
            $mode = $request->get('map_view');

            UserPref::updateOrCreate(
                ['user_id' => $request->user()->user_id, 'pref' => 'availability_map_view'],
                ['value' => $mode]
            );

            return ['map_view' => $mode];
        }

        if ($request->has('group_view')) {
            $group = $request->get('group_view');

            UserPref::updateOrCreate(
                ['user_id' => $request->user()->user_id, 'pref' => 'availability_map_group'],
                ['value' => $group]
            );

            return ['group_view' => $group];
        }

        return [];
    }
}
