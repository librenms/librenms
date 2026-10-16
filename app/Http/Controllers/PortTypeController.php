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

use App\Facades\LibrenmsConfig;
use App\Models\Port;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PortTypeController extends Controller
{
    /**
     * Port types whose matching descriptions can be extended with a <type>_descr config setting.
     */
    private const CONFIGURABLE_TYPES = ['core', 'customers', 'peering', 'transit'];

    public function graph(Request $request, string $types): View
    {
        $this->authorize('viewAny', Port::class);

        $types = explode(',', (string) $types);

        $ports = Port::hasAccess($request->user())
            ->whereIn('port_descr_type', $this->expandTypes($types))
            ->with('device')
            ->withCount('macAccounting')
            ->orderBy('ifAlias')
            ->get();

        return view('porttype', ['types' => $types, 'ports' => $ports]);
    }

    /**
     * Expand the requested types with the matching *_descr settings, so that for example
     * "peering" also matches ports with the "ix" type when peering_descr contains "ix".
     *
     * @param  string[]  $types
     * @return string[]
     */
    private function expandTypes(array $types): array
    {
        $searchTypes = [];

        foreach ($types as $type) {
            $searchTypes[] = $type;

            if (in_array($type, self::CONFIGURABLE_TYPES, true)) {
                foreach (Arr::wrap(LibrenmsConfig::get($type . '_descr', [])) as $descr) {
                    $searchTypes[] = (string) $descr;
                }
            }
        }

        return array_values(array_unique(array_filter($searchTypes, fn ($type) => $type !== '')));
    }
}
