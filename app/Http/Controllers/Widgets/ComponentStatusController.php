<?php
/**
 * ComponentStatusController.php
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

namespace App\Http\Controllers\Widgets;

use App\Models\Component;
use DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComponentStatusController extends WidgetController
{
    protected $title = 'Component Status';

    /**
     * @param Request $request
     * @return View
     */
    public function getView(Request $request)
    {
        $status = [
            [
                'color' => 'text-success',
                'text' => __('Ok'),
            ],
            [
                'color' => 'grey',
                'text' => __('Warning'),
            ],
            [
                'color' => 'text-danger',
                'text' => __('Critical'),
            ]
        ];

        $component_status = Component::query()
            ->select('status', DB::raw("count('status') as total"))
            ->groupBy('status')
            ->get()->pluck('total', 'status')->toArray();

        foreach ($status as $key => $value) {
            $status[$key]['total'] = isset($component_status[$key]) ? $component_status[$key] : 0;
        }

        return view('widgets.component-status', compact('status'));
    }
}
