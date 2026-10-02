<?php

/**
 * CefController.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 LibreNMS
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Routing;

use App\Http\Controllers\Controller;
use App\Models\CefSwitching;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CefController extends Controller
{
    public function __invoke(Request $request): View
    {
        $request->validate([
            'view' => 'nullable|in:basic,graphs',
        ]);

        $view = $request->query('view', 'basic');

        return view('routing.cef', [
            'view' => $view,
            'cef_options' => [
                'basic' => ['text' => __('Basic'), 'link' => route('routing.cef')],
                'graphs' => ['text' => __('Graphs'), 'link' => route('routing.cef', ['view' => 'graphs'])],
            ],
            'cefs' => CefSwitching::hasAccess($request->user())
                ->orderBy('device_id')
                ->orderBy('entPhysicalIndex')
                ->orderBy('afi')
                ->orderBy('cef_index')
                ->get(),
        ]);
    }
}
