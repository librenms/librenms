<?php
/**
 * GraphController.php
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
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LibreNMS\Util\Graph;
use LibreNMS\Util\StringHelpers;

class GraphController extends Controller
{
    private $rules = [
        'limit' => 'int',
        'page' => 'int',
        'term' => 'nullable|string',
        'device' => 'nullable|int',
    ];

    public function __invoke(Request $request)
    {
        $this->validate($request, $this->rules);

        $data = [];
        $search = $request->get('term');
        $device_id = $request->get('device');
        $device = $device_id ? Device::find($device_id) : null;

        foreach (Graph::getTypes() as $type) {
            $graphs = $this->filterTypeGraphs(collect(Graph::getSubtypes($type, $device)), $type, $search);

            if ($graphs->isNotEmpty()) {
                $data[] = [
                    'text' => StringHelpers::niceCase($type),
                    'children' => $graphs->map(function ($graph) use ($type) {
                        return $this->formatGraph($type, $graph);
                    })->values(),
                ];
            }
        }

        $aggregators = $this->filterTypeGraphs(collect([
            'transit' => 'Transit',
            'peering' => 'Peering',
            'core' => 'Core',
            'custom' => 'Custom',
            'ports' => 'Manual Ports',
        ]), 'aggregators', $search);
        if ($aggregators->isNotEmpty()) {
            $data[] = [
                'text' => 'Aggregators',
                'children' => $aggregators->map(function ($text, $id) {
                    return compact('id', 'text');
                })->values(),
            ];
        }

        $billing = $this->filterTypeGraphs(collect([
            'bill_bits' => 'Bill Bits',
        ]), 'bill', $search);
        if ($billing->isNotEmpty()) {
            $data[] = [
                'text' => 'Bill',
                'children' => $billing->map(function ($text, $id) {
                    return compact('id', 'text');
                })->values(),
            ];
        }

        return response()->json([
            'results' => $data,
            'pagination' => ['more' => false],
        ]);
    }

    private function formatGraph($top, $graph)
    {
        $text = $graph;
        if (Str::contains('_', $graph)) {
            [$type, $subtype] = explode('_', $graph, 2);
        } else {
            $type = $graph;
            $subtype = '';
        }

        if (! Graph::isMibGraph($type, $subtype)) {
            $text = ucwords($top . ' ' . str_replace(['_', '-'], ' ', $text));
        }

        return [
            'id' => $top . '_' . $graph,
            'text' => $text,
        ];
    }

    /**
     * @param Collection $graphs
     * @param string $type
     * @param string $search
     * @return Collection
     */
    private function filterTypeGraphs($graphs, $type, $search)
    {
        $search = strtolower($search);

        if ($search) {
            $terms = preg_split('/[ _-]/', $search, 2);
            $first = array_shift($terms);

            if (Str::contains($type, $first)) {
                // search matches type, show all unless there are more terms.
                if (! empty($terms)) {
                    $sub_search = array_shift($terms);
                    $graphs = $graphs->filter(function ($graph) use ($sub_search) {
                        return Str::contains(strtolower($graph), $sub_search);
                    });
                }
            } else {
                // if the type matches, don't filter the sub values
                $graphs = $graphs->filter(function ($graph) use ($search) {
                    return Str::contains(strtolower($graph), $search);
                });
            }
        }

        return $graphs;
    }
}
