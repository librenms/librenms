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

    //TODO: Add fetch for map data

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

        $data = [
            'map_id' => $request->map_id,
            'edit' => true,
            'vmargin' => 20,
            'hmargin' => 20,
        ];

        if(is_null($request->map_id)) {
            $data['maps'] = CustomMap::orderBy('name')->get(['custom_map_id','name']);
        } elseif($request->map_id == 0) {
            $data['name'] = 'New Map';
            $data['map_conf'] = [
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
            $data['background'] = null;
        } else {
            $map = CustomMap::find($request->map_id);
            if(! $map) {
                abort(404);
            }
            $data['name'] = $map->name;
            $data['newedge_conf'] = $map->newedgeconfig;
            $data['newnode_conf'] = $map->newnodeconfig;
            $data['map_conf'] = $map->options;
            $data['map_conf']['width'] = $map->width;
            $data['map_conf']['height'] = $map->height;
            // Override some settings for the editor
            $data['map_conf']['interaction'] = ['dragNodes' => true, 'dragView' => false, 'zoomView' => false];
            $data['map_conf']['manipulation'] = ['enabled' => true, 'initiallyActive' => true];
            $data['map_conf']['physics'] = ['enabled' => false];
            $data['background'] = $this->checkImageCache($map);
        }

        return view('map.custom', $data);
    }

    public function save(Request $request)
    {
        $errors = [];

        $map = CustomMap::where('custom_map_id', '=', $request->map_id)->with('nodes', 'edges')->first();
        if(!$map) {
            abort(404);
        }

        if (! $errors) {
            DB::transaction(function() use ($map, $request) {
                $nodesProcessed = [];
                $edgesProcessed = [];

                $newNodes = [];

                $newnodeconf = json_decode($request->post('newnodeconf'));
                $newedgeconf = json_decode($request->post('newedgeconf'));
                $nodes = json_decode($request->nodes);
                $edges = json_decode($request->edges);

                $map->newedgeconfig = $newedgeconf;
                $map->newnodeconfig = $newnodeconf;
                $map->save();

                foreach ($nodes as $nodeid => $node) {
                    if (strpos($nodeid, 'new') == 0) {
                        $dbnode = new CustomMapNode;
                        $dbnode->map()->associate($map);
                    } else {
                        $dbnode = $map->nodes[$nodeid];
                    }
                    $dbnode->device_id = $node->title ? $node->title : null;
                    $dbnode->label = $node->label;
                    $dbnode->style = $node->shape;
                    $dbnode->icon = $node->icon;
                    $dbnode->size = $node->size;
                    $dbnode->text_face = $node->font->face;
                    $dbnode->text_size = $node->font->size;
                    $dbnode->text_colour = $node->font->color;
                    $dbnode->colour_bg = (array)$node->color ? $node->color->background : null;
                    $dbnode->colour_bdr = (array)$node->color ? $node->color->border : null;
                    $dbnode->border_width = $node->borderWidth;
                    $dbnode->x_pos = intval($node->x);
                    $dbnode->y_pos = intval($node->y);

                    $dbnode->save();
                    $nodesProcessed[$dbnode->custom_map_node_id] = true;
                    $newNodes[$nodeid] = $dbnode;
                }
                foreach ($edges as $edgeid => $edge) {
                    if (strpos($edgeid, 'new') == 0) {
                        $dbedge = new CustomMapEdge;
                        $dbedge->map()->associate($map);
                    } else {
                        $dbedge = $map->edges[$edgeid];
                    }
                    $dbedge->custom_map_node1_id = strpos($edge->from, "new") == 0 ? $newNodes[$edge->from]->custom_map_node_id : $edge->from;
                    $dbedge->custom_map_node2_id = strpos($edge->to, "new") == 0 ? $newNodes[$edge->to]->custom_map_node_id : $edge->to;
                    $dbedge->port_id = $edge->port_id ? $edge->port_id : null;
                    $dbedge->reverse= $edge->reverse;
                    $dbedge->showpct = $edge->showpct;
                    $dbedge->style = $edge->style;
                    $dbedge->text_face = $edge->text_face;
                    $dbedge->text_size = $edge->text_size;
                    $dbedge->text_colour = $edge->text_colour;
                    $dbedge->mid_x = intval($edge->mid_x);
                    $dbedge->mid_y = intval($edge->mid_y);

                    $dbedge->save();
                    $edgeProcessed[$dbedge->custom_map_edge_id] = true;
                }
                foreach ($map->edges as $edge) {
                    if (! array_key_exists($edge->custom_map_edge_id, $edgesProcessed)) {
                        $edge->delete();
                    }
                }
                foreach ($map->nodes as $node) {
                    if (! array_key_exists($node->custom_map_node_id, $nodesProcessed)) {
                        $node->delete();
                    }
                }
            });
        }
        return response()->json(['id' => $map->custom_map_id, 'errors' => $errors]);
    }

    public function saveSettings(Request $request)
    {
        $errors = [];

        $map_id = $request->map_id;
        $name = $request->post('name');
        $width = $request->post('width');
        $height = $request->post('height');
        $bgclear = $request->post('bgclear') == 'true' ? true : false;
        $bgnewimage = $request->post('bgimage');


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
                $map->newnodeconfig = $this->default_node_conf;
                $map->newedgeconfig = $this->default_edge_conf;
                $map->background_version = 0;
            } else {
                $map = CustomMap::find($map_id);
            }

            $map->name = $name;
            $map->width = $width;
            $map->height = $height;
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
