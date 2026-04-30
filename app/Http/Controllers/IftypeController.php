<?php

/*
 * IftypeController.php
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
 * @copyright  2026 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.au>
 */

namespace App\Http\Controllers;

use App\Models\Port;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IftypeController extends Controller
{
    public function index(Request $request, string $vars): View
    {
        $vars = array_reduce(
            explode('/', $vars),
            function ($output, $var) {
                [$key, $val] = explode('=', $var, 2);
                $output[$key] = $val;

                return $output;
            },
            []
        );

        if (isset($vars['type'])) {
            $types = explode(',', (string) $vars['type']);
            $ports = Port::whereIn('port_descr_type', $types);
        } elseif (isset($vars['group'])) {
            $types = ['Group ' . $vars['group']];
            $ports = Port::inPortGroup($vars['group']);
        } else {
            return view('iftype', ['types' => [], 'ports' => [], 'allPortIds' => []]);
        }
        $ports = $ports->hasAccess(Auth::user())->with('device')->withCount('macAccounting')->get();

        return view('iftype', ['types' => $types, 'ports' => $ports]);
    }
}
