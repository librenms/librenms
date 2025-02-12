<?php
/**
 * CustomMapController.php
 *
 * Controller for custom maps
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
 * @copyright  2023 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.com.au>
 */

namespace App\Http\Controllers\Maps;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomMapSettingsRequest;
use App\Models\CustomMap;
use App\Models\CustomMapNodeImage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use LibreNMS\Config;

class CustomMapController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CustomMap::class, 'map');
    }

    public function index(): View
    {
        return view('map.custom-manage', [
            'maps' => CustomMap::orderBy('name')->get(['custom_map_id', 'name', 'menu_group'])->groupBy('menu_group')->sortKeys(),
            'name' => 'New Map',
            'menu_group' => null,
            'node_align' => Config::get('custom_map.node_align', 10),
            'edge_separation' => Config::get('custom_map.edge_seperation', 10),
            'reverse_arrows' => Config::get('custom_map.reverse_arrows', false) ? 'true' : 'false',
            'legend' => [
                'x' => -1,
                'y' => -1,
            ],
            'background_type' => Config::get('custom_map.background_type', 'none'),
            'background_data' => Config::get('custom_map.background_data'),
            'map_conf' => [
                'width' => Config::get('custom_map.width', '1800px'),
                'height' => Config::get('custom_map.height', '800px'),
                'interaction' => [
                    'dragNodes' => true,
                    'dragView' => false,
                    'zoomView' => false,
                ],
                'manipulation' => [
                    'enabled' => true,
                    'initiallyActive' => true,
                ],
                'physics' => [
                    'enabled' => false,
                ],
            ],
            'map_options' => [
                'interaction' => [
                    'dragNodes' => false,
                    'dragView' => false,
                    'zoomView' => false,
                ],
                'manipulation' => [
                    'enabled' => false,
                ],
                'physics' => [
                    'enabled' => false,
                ],
            ],
        ]);
    }

    public function destroy(CustomMap $map): Response
    {
        $map->delete();

        return response('Success', 200)
                  ->header('Content-Type', 'text/plain');
    }

    public function show(Request $request, CustomMap $map): View
    {
        $request->validate([
            'screenshot' => 'nullable|in:yes',
        ]);

        $screenshot = $request->input('screenshot') === 'yes' ? 1 : 0;

        $map_conf = $map->options;
        $map_conf['width'] = $map->width;
        $map_conf['height'] = $map->height;

        return view('map.custom-view', [
            'edit' => false,
            'map_id' => $map->custom_map_id,
            'name' => $map->name,
            'menu_group' => $map->menu_group,
            'reverse_arrows' => $map->reverse_arrows,
            'legend' => $this->legendConfig($map),
            'background_type' => $map->background_type,
            'background_config' => $map->getBackgroundConfig(),
            'page_refresh' => Config::get('page_refresh', 300),
            'map_conf' => $map_conf,
            'base_url' => Config::get('base_url'),
            'newedge_conf' => $map->newedgeconfig,
            'newnode_conf' => $map->newnodeconfig,
            'vmargin' => 20,
            'hmargin' => 20,
            'screenshot' => $screenshot,
        ]);
    }

    public function edit(CustomMap $map): View
    {
        $data = [
            'map_id' => $map->custom_map_id,
            'name' => $map->name,
            'menu_group' => $map->menu_group,
            'node_align' => $map->node_align,
            'edge_separation' => $map->edge_separation,
            'reverse_arrows' => $map->reverse_arrows,
            'legend' => $this->legendConfig($map),
            'newedge_conf' => $map->newedgeconfig,
            'newnode_conf' => $map->newnodeconfig,
            'map_conf' => $map->options,
            'map_options' => $map->options,
            'background_type' => $map->background_type,
            'background_config' => $map->getBackgroundConfig(),
            'edit' => true,
            'vmargin' => 20,
            'hmargin' => 20,
            'base_url' => Config::get('base_url'),
            'images' => $this->listNodeImages(),
            'maps' => CustomMap::orderBy('name')->where('custom_map_id', '<>', $map->custom_map_id)->get(['custom_map_id', 'name']),
        ];

        $data['map_conf']['width'] = $map->width;
        $data['map_conf']['height'] = $map->height;
        // Override some settings for the editor
        $data['map_conf']['interaction'] = ['dragNodes' => true, 'dragView' => false, 'zoomView' => false, 'multiselect' => true];
        $data['map_conf']['manipulation'] = ['enabled' => true, 'initiallyActive' => true];
        $data['map_conf']['physics'] = ['enabled' => false];

        return view('map.custom-edit', $data);
    }

    public function store(CustomMapSettingsRequest $request): JsonResponse
    {
        // create a new map with default values
        $map = new CustomMap;
        $map->options = [
            'interaction' => [
                'dragNodes' => false,
                'dragView' => false,
                'zoomView' => false,
            ],
            'manipulation' => [
                'enabled' => false,
            ],
            'physics' => [
                'enabled' => false,
            ],
        ];
        $map->newnodeconfig = [
            'borderWidth' => 1,
            'color' => [
                'border' => Config::get('custom_map.node_border', '#2B7CE9'),
                'background' => Config::get('custom_map.node_background', '#D2E5FF'),
            ],
            'font' => [
                'color' => Config::get('custom_map.node_font_color', '#343434'),
                'size' => Config::get('custom_map.node_font_size', 14),
                'face' => Config::get('custom_map.node_font_face', 'arial'),
            ],
            'icon' => [],
            'label' => true,
            'shape' => Config::get('custom_map.node_type', 'box'),
            'size' => Config::get('custom_map.node_size', 25),
        ];
        $map->newedgeconfig = [
            'arrows' => [
                'to' => [
                    'enabled' => true,
                ],
            ],
            'smooth' => [
                'type' => 'dynamic',
            ],
            'font' => [
                'color' => Config::get('custom_map.edge_font_color', '#343434'),
                'size' => Config::get('custom_map.edge_font_size', 12),
                'face' => Config::get('custom_map.edge_font_face', 'arial'),
                'align' => Config::get('custom_map.edge_font_align', 'horizontal'),
            ],
            'label' => true,
        ];
        $map->background_type = Config::get('custom_map.background_type', 'none');
        $map->background_data = Config::get('custom_map.background_data');
        $map->legend_colours = $this->getDefaultLegendColours();
        if ($map->legend_colours) {
            $map->legend_steps = count($map->legend_colours) - 2;
        }

        return $this->update($request, $map);
    }

    public function update(CustomMapSettingsRequest $request, CustomMap $map): JsonResponse
    {
        $map->fill($request->validated());
        $map->options = json_decode($request->options);
        $map->save(); // save to get ID

        return response()->json([
            'id' => $map->custom_map_id,
            'name' => $map->name,
            'menu_group' => $map->menu_group,
            'width' => $map->width,
            'height' => $map->height,
            'reverse_arrows' => $map->reverse_arrows,
            'edge_separation' => $map->edge_separation,
            'options' => $map->options,
        ]);
    }

    public function clone(CustomMap $map): JsonResponse
    {
        $newmap = $map->replicate();
        $newmap->name .= ' - Clone';

        if ($map->background) {
            $newbackground = $map->background->replicate();
        } else {
            $newbackground = null;
        }

        $nodes = $map->nodes()->get();
        $edges = $map->edges()->get();

        DB::transaction(function () use ($newmap, $newbackground, $nodes, $edges) {
            $newmap->save();

            if ($newbackground) {
                $newbackground->custom_map_id = $newmap->custom_map_id;
                $newbackground->save();
            }

            $node_id_map = collect();
            foreach ($nodes as $id => $node) {
                $newnode = $node->replicate();
                $newnode->custom_map_id = $newmap->custom_map_id;
                $newnode->save();

                $node_id_map->put($node->custom_map_node_id, $newnode->custom_map_node_id);
            }

            foreach ($edges as $id => $edge) {
                $newedge = $edge->replicate();
                $newedge->custom_map_id = $newmap->custom_map_id;
                $newedge->custom_map_node1_id = $node_id_map->get($edge->custom_map_node1_id);
                $newedge->custom_map_node2_id = $node_id_map->get($edge->custom_map_node2_id);
                $newedge->save();
            }
        });

        return response()->json([
            'id' => $newmap->custom_map_id,
        ]);
    }

    /**
     * Get a list of all available node images with a label.
     */
    private function listNodeImages(): array
    {
        $images = [];
        $image_translations = __('map.custom.edit.node.image_options');

        foreach (Storage::disk('base')->files('html/images/custommap/icons') as $image) {
            if (in_array(strtolower(pathinfo($image, PATHINFO_EXTENSION)), ['svg', 'png', 'jpg', 'gif'])) {
                $file = pathinfo($image, PATHINFO_BASENAME);
                $filename = pathinfo($image, PATHINFO_FILENAME);

                $images[$file] = $image_translations[$filename] ?? ucwords(str_replace(['-', '_'], [' - ', ' '], $filename));
            }
        }

        foreach (CustomMapNodeImage::all() as $image) {
            $images[$image->custom_map_node_image_id] = $image->name;
        }

        asort($images);

        return $images;
    }

    /**
     * Return the legend config
     */
    private function legendConfig(CustomMap $map): array
    {
        $legend = [
            'x' => $map->legend_x,
            'y' => $map->legend_y,
            'steps' => $map->legend_steps,
            'hide_invalid' => $map->legend_hide_invalid,
            'hide_overspeed' => $map->legend_hide_overspeed,
            'font_size' => $map->legend_font_size,
            'colours' => $map->legend_colours,
        ];

        return $legend;
    }

    /**
     * Return the default legend colours
     */
    private function getDefaultLegendColours(): array|null
    {
        $ret = Config::get('custom_map.legend_colours', null);

        // Return null if there is no config
        if (! $ret) {
            return null;
        }

        foreach (array_keys($ret) as $key) {
            if (! is_numeric($key)) {
                // Delete keys that are not numeric
                unset($ret[$key]);
            } elseif (! preg_match('/^#[A-Fa-f0-0]{6}$/', $ret[$key])) {
                // Delete keys that are not a valid hex HTML colour
                unset($ret[$key]);
            }
        }

        // Make sure a value exists for device down
        if (! array_key_exists('-2', $ret)) {
            $ret['-2'] = '#8B0000';
        }

        // Make sure a value exists for invalid
        if (! array_key_exists('-1', $ret)) {
            $ret['-1'] = '#000000';
        }

        // Make sure a value exists for 0
        if (! array_key_exists('0', $ret)) {
            $ret['0'] = '#00FF00';
        }

        return $ret;
    }
}
