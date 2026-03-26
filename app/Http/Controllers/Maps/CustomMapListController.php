<?php

/**
 * CustomMapListController.php
 *
 * Controller for listing custom maps
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
 * @copyright  2025 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.au>
 */

namespace App\Http\Controllers\Maps;

use App\Http\Controllers\Controller;
use App\Models\CustomMap;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CustomMapListController extends Controller
{
    public function index(Request $request): View
    {
        $group = $request->input('group') ?? '';

        return view('map.custom-list', [
            'maps' => CustomMap::hasAccess($request->user())->orderBy('name')->get(['custom_map_id', 'name', 'menu_group'])->groupBy('menu_group')->sortKeys(),
            'open_group' => $group,
        ]);
    }
}
