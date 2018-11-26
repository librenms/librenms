<?php
/**
 * LocationsController.php
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

namespace App\Http\Controllers\Table;

use App\Models\Location;

class LocationController extends TableController
{

    /**
     * Defines the base query for this resource
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function baseQuery($request)
    {
        return Location::hasAccess($request->user());
    }

    /**
     * @param Location $location
     * @return array|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection
     */
    public function formatItem($location)
    {
        $edit = '<button type="button" class="btn btn-primary" data-id="' . $location->id .
            '" data-location="' . $location->location . '" data-lat="' . $location->lat . '" data-lng="' .
            $location->lng . '" onclick="$(\'#edit-location\').modal(\'show\', this)"><i class="fa fa-pencil" aria-hidden="true"></i>' .
            '<span class="hidden-sm"> Edit</span></button>';


        $delete = ' <button type="button" class="btn btn-danger" onclick="delete_location(' . $location->id . ')"';
        if ($location->devices()->exists()) {
            $delete .= 'disabled title="Cannot delete locations used by devices"';
        }

        $delete .= '><i class="fa fa-trash" aria-hidden="true"></i><span class="hidden-sm"> Delete</span></button>';



        return [
            'location' => $location->location,
            'coordinates' => $location->hasCoordinates() ? $location->lat . ', ' . $location->lng : 'N/A',
            'alert' => $location->devices()->isDown()->count() ? '<i class="fa fa-flag" style="color:red" aria-hidden="true"></i>' : '',
            'devices' => '<span class="label label-primary">' . $location->devices()->count() . '</span>',
            'network' => '<span class="label label-default">' . $location->devices()->where('type', 'network')->count() . '</span>',
            'servers' => '<span class="label label-default">' . $location->devices()->where('type', 'server')->count() . '</span>',
            'firewalls' => '<span class="label label-default">' . $location->devices()->where('type', 'firewall')->count() . '</span>',
            'actions' => '<span style="white-space:nowrap">' . $edit . $delete . '</span>',
        ];
    }
}
