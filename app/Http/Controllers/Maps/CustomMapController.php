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
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
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
            'maps' => CustomMap::orderBy('name')->get(['custom_map_id', 'name']),
            'name' => 'New Map',
            'node_align' => 10,
            'background' => null,
            'map_conf' => [
                'height' => '800px',
                'width' => '1800px',
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
        ]);
    }

    public function destroy(CustomMap $map): Response
    {
        $map->delete();

        return response('Success', 200)
                  ->header('Content-Type', 'text/plain');
    }

    public function show(CustomMap $map): View
    {
        $map_conf = $map->options;
        $map_conf['width'] = $map->width;
        $map_conf['height'] = $map->height;
        $data = [
            'edit' => false,
            'map_id' => $map->custom_map_id,
            'name' => $map->name,
            'background' => (bool) $map->background_suffix,
            'bgversion' => $map->background_version,
            'page_refresh' => Config::get('page_refresh', 300),
            'map_conf' => $map_conf,
            'base_url' => Config::get('base_url'),
            'newedge_conf' => $map->newedgeconfig,
            'newnode_conf' => $map->newnodeconfig,
            'vmargin' => 20,
            'hmargin' => 20,
        ];

        return view('map.custom-view', $data);
    }

    public function edit(CustomMap $map): View
    {
        $data = [
            'map_id' => $map->custom_map_id,
            'name' => $map->name,
            'node_align' => $map->node_align,
            'newedge_conf' => $map->newedgeconfig,
            'newnode_conf' => $map->newnodeconfig,
            'map_conf' => $map->options,
            'background' => (bool) $map->background_suffix,
            'bgversion' => $map->background_version,
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
        $data['map_conf']['interaction'] = ['dragNodes' => true, 'dragView' => false, 'zoomView' => false];
        $data['map_conf']['manipulation'] = ['enabled' => true, 'initiallyActive' => true];
        $data['map_conf']['physics'] = ['enabled' => false];

        return view('map.custom-edit', $data);
    }

    public function store(CustomMapSettingsRequest $request): JsonResponse
    {
        return $this->update($request, new CustomMap);
    }

    public function update(CustomMapSettingsRequest $request, CustomMap $map): JsonResponse
    {
        $map->fill($request->validated());
        $map->save(); // save to get ID

        return response()->json([
            'id' => $map->custom_map_id,
            'name' => $map->name,
            'width' => $map->width,
            'height' => $map->height,
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

        return $images;
    }
}
