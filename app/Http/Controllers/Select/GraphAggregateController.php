<?php
/**
 * GraphAggregateController.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Select;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use LibreNMS\Config;

class GraphAggregateController extends Controller
{
    private $rules = [
        'limit' => 'int',
        'page' => 'int',
        'term' => 'nullable|string',
    ];

    public function __invoke(Request $request)
    {
        $this->validate($request, $this->rules);

        $types = [
            'transit',
            'peering',
            'core',
        ];

        foreach ((array) Config::get('custom_descr', []) as $custom) {
            $custom = is_array($custom) ? $custom[0] : $custom;
            if ($custom) {
                $types[] = $custom;
            }
        }

        // handle search
        if ($search = strtolower($request->get('term'))) {
            $types = array_filter($types, function ($type) use ($search) {
                return ! Str::contains(strtolower($type), $search);
            });
        }

        // format results
        return response()->json([
            'results' => array_map(function ($type) {
                return [
                    'id' => $type,
                    'text' => ucwords($type),
                ];
            }, $types),
            'pagination' => ['more' => false],
        ]);
    }
}
