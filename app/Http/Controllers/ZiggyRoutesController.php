<?php

/**
 * ZiggyRoutesController.php
 *
 * Serve Ziggy routes and the route() function as a cacheable script
 * instead of inlining them into every page with @routes.
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Tighten\Ziggy\Ziggy;

class ZiggyRoutesController extends Controller
{
    public function __invoke(): Response
    {
        $routeFunction = file_get_contents(base_path('vendor/tightenco/ziggy/dist/route.umd.js'));

        return response('globalThis.Ziggy=' . (new Ziggy)->toJson() . ';' . $routeFunction)
            ->header('Content-Type', 'application/javascript; charset=utf-8');
    }
}
