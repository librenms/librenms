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
use App\Models\CustomMap;
use App\Models\CustomMapBackground;
use App\Models\CustomMapEdge;
use App\Models\CustomMapNode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use LibreNMS\Config;
use LibreNMS\Util\Url;

class CustomMapController extends Controller
{
    private $default_edge_conf = [
        'arrows' => [
            'to' => [
                'enabled' => true,
            ],
        ],
        'smooth' => [
            'type' => "dynamic",
        ],
        'font' => [
            'color' => '#343434',
            'size' => 14,
            'face' => 'arial',
        ],
        'label' => true,
    ];

    private $default_node_conf = [
        'borderWidth' => 1,
        'color' => [
            'border' => '#2B7CE9',
            'background' => '#D2E5FF',
        ],
        'font' => [
            'color' => '#343434',
            'size' => 14,
            'face' => 'arial',
        ],
        'icon' => [],
        'label' => true,
        'shape' => 'box',
        'size' => 25,
    ];

    private $default_map_options = [
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

    private function checkImageCache(CustomMap $map)
    {
        if(! $map->custom_map_id) {
           return null;
        }
        if(! $map->background_suffix) {
           return null;
        }

        $imageName = $map->custom_map_id . '_' . $map->background_version . '.' . $map->background_suffix;
        if(Storage::disk('base')->missing('html/images/custommap/' . $imageName)) {
            Storage::disk('base')->put('html/images/custommap/' . $imageName, $map->background->background_image);
        }
        return $imageName;
    }

    public function background(Request $request)
    {
        $map = CustomMap::where('custom_map_id', '=', $request->map_id)
            ->hasAccess($request->user())
            ->first();

        $background = $this->checkImageCache($map);
        if($background) {
            $path = Storage::disk('base')->path('html/images/custommap/') . $background;
            $mime = Storage::mimeType($background);
            $headers = [
                'Content-Type' => $mime,
            ];
            return response()->file($path, $headers);
        }
        abort(404);
    }

    public function view(Request $request)
    {
        $map = CustomMap::where('custom_map_id', '=', $request->map_id)
            ->hasAccess($request->user())
            ->first();

        if(!$map) {
            abort(404);
        }

        $name = $map->name;
        $newedge_conf = $map->newedgeconfig;
        $newnode_conf = $map->newnodeconfig;
        $map_conf = $map->options;
        $map_conf['width'] = $map->width;
        $map_conf['height'] = $map->height;
        $background = $this->checkImageCache($map);

        $data = [
            'edit' => false,
            'map_id' => $request->map_id,
            'name' => $name,
            'background' => $background,
            'page_refresh' => Config::get('page_refresh', 300),
            'map_conf' => $map_conf,
            'newedge_conf' => $newedge_conf,
            'newnode_conf' => $newnode_conf,
            'vmargin' => 20,
            'hmargin' => 20,
        ];

        return view('map.custom', $data);
    }

    public function edit(Request $request)
    {
        if (! $request->user()->isAdmin()) {
            return response('Insufficient privileges');
        }

        if($request->map_id == 0) {
            $newedge_conf = $this->default_edge_conf;
            $newnode_conf = $this->default_node_conf;
            $name = 'New Map';
            $map_conf = [
                'height' => "800px",
                'width' => "1800px",
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
            ];
            $background = null;
        } else {
            $map = CustomMap::find($request->map_id);
            $name = $map->name;
            $newedge_conf = $map->newedgeconfig;
            $newnode_conf = $map->newnodeconfig;
            $map_conf = $map->options;
            $map_conf['width'] = $map->width;
            $map_conf['height'] = $map->height;
            $background = $this->checkImageCache($map);
        }

        $data = [
            'edit' => true,
            'map_id' => $request->map_id,
            'name' => $name,
            'background' => $background,
            'page_refresh' => Config::get('page_refresh', 300),
            'map_conf' => $map_conf,
            'newedge_conf' => $newedge_conf,
            'newnode_conf' => $newnode_conf,
            'vmargin' => 20,
            'hmargin' => 20,
        ];

        return view('map.custom', $data);
    }

    public function save(Request $request)
    {
        $errors = [];

        $map_id = $request->map_id;
        $name = $request->post('name');
        $width = $request->post('width');
        $height = $request->post('height');
        $bgclear = $request->post('bgclear') == 'true' ? true : false;
        $bgnewimage = $request->post('bgimage');
        $newnodeconf = json_decode($request->post('newnodeconf'));
        $newedgeconf = json_decode($request->post('newedgeconf'));


        if (! preg_match('/^(\d+)(px|%)$/', $width, $matches)) {
            array_push($errors, "Width must be a number followed by px or %");
        } elseif ($matches[2] == 'px' && $matches[1] < 200) {
            array_push($errors, "Width in pixels must be at least 200");
        } elseif ($matches[2] == '%' && ($matches[1] < 10 || $matches[1] > 100)) {
            array_push($errors, "Width percent must be between 10 and 100");
        }

        if (! preg_match('/^(\d+)(px|%)$/', $height, $matches)) {
            array_push($errors, "Height must be a number followed by px or %");
        } elseif ($matches[2] == 'px' && $matches[1] < 200) {
            array_push($errors, "Height in pixels must be at least 200");
        } elseif ($matches[2] == '%' && ($matches[1] < 10 || $matches[1] > 100)) {
            array_push($errors, "Height percent must be between 10 and 100");
        }

        if (! $name) {
            array_push($errors, "Name must be supplied");
        }

        if ($bgnewimage) {
            $request->validate(['bgimage' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048']);
        }

        $imageName = null;
        if (! $errors) {
            if (! $map_id) {
                $map = new CustomMap;
                $map->options = $this->default_map_options;
                $map->background_version = 0;
            } else {
                $map = CustomMap::find($map_id);
            }

            $map->name = $name;
            $map->width = $width;
            $map->height = $height;
            $map->newnodeconfig = $newnodeconf;
            $map->newedgeconfig = $newedgeconf;
            $map->save();
            if (! $map_id) {
                $map_id = $map->custom_map_id;
            }

            if ($request->bgimage) {
                $map->background_suffix = $request->bgimage->extension();
                if(!$map->background) {
                    $background = new CustomMapBackground;
                    $background->background_image = $request->bgimage->getContent();
                    $map->background()->save($background);
                } else {
                    $map->background->background_image = $request->bgimage->getContent();
                    $map->background->save();
                }
                $map->background_version++;
                $map->save();
                $map->refresh();
            } elseif ($bgclear) {
                if($map->background) {
                    $map->background->delete();
                }
                $map->background_suffix = null;
                $map->save();
                $map->refresh();
            }
            $imageName = $this->checkImageCache($map);
        }

        return response()->json(['id' => $map_id, 'bgimage' => $imageName, 'errors' => $errors]);
    }
}
