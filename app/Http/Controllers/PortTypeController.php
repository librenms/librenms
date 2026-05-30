<?php

/*
 * PortTypeController.php
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

class PortTypeController extends Controller
{
    public function graph(Request $request, string $types): View
    {
        $this->authorize('viewAny', Port::class);

        $types = explode(',', (string) $types);
        $ports = Port::hasAccess($request->user())->whereIn('port_descr_type', $types)->with('device')->withCount('macAccounting')->get();

        return view('porttype', ['types' => $types, 'ports' => $ports]);
    }
}
