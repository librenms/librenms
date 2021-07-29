<?php
/**
 * PortGroupController.php
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
 * @link       http://librenms.org
 * @copyright  2020 Thomas Berberich
 * @author     Thomas Berberich <sourcehhdoctor@gmail.com>
 */

namespace App\Http\Controllers\Select;

use App\Models\PortGroup;

class PortGroupController extends SelectController
{
    protected function searchFields($request)
    {
        return ['name'];
    }

    protected function baseQuery($request)
    {
        return PortGroup::hasAccess($request->user())->select(['id', 'name']);
    }

    /**
     * @param PortGroup $port_group
     */
    public function formatItem($port_group)
    {
        return [
            'id' => $port_group->id,
            'text' => $port_group->name,
        ];
    }
}
