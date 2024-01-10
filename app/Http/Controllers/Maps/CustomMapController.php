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
            'type' => 'dynamic',
        ],
        'font' => [
            'color' => '#343434',
            'size' => 12,
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

    protected function nodeDisabledStyle()
    {
        return [
            'border' => Config::get('network_map_legend.di.border'),
            'background' => Config::get('network_map_legend.di.node'),
        ];
    }

    protected function nodeDownStyle()
    {
        return [
            'border' => Config::get('network_map_legend.dn.border'),
            'background' => Config::get('network_map_legend.dn.node'),
        ];
    }

    protected function nodeUpStyle()
    {
        return [
            'border' => null,
            'background' => null,
        ];
    }

    private function checkImageCache(CustomMap $map)
    {
        if (! $map->background_suffix) {
            return null;
        }

        $imageName = $map->custom_map_id . '_' . $map->background_version . '.' . $map->background_suffix;
        if (Storage::disk('base')->missing('html/images/custommap/background/' . $imageName)) {
            Storage::disk('base')->put('html/images/custommap/background/' . $imageName, $map->background->background_image);
        }

        return $imageName;
    }

    public function delete(Request $request)
    {
        $map = CustomMap::where('custom_map_id', '=', $request->map_id)
            ->hasAccess($request->user())
            ->first();

        if (! $map) {
            abort(404);
        }

        $map->delete();

        return response('Success', 200)
                  ->header('Content-Type', 'text/plain');
    }

    public function background(Request $request)
    {
        $map = CustomMap::where('custom_map_id', '=', $request->map_id)
            ->hasAccess($request->user())
            ->first();

        $background = $this->checkImageCache($map);
        if ($background) {
            $path = Storage::disk('base')->path('html/images/custommap/background/') . $background;
            $mime = Storage::mimeType($background);
            $headers = [
                'Content-Type' => $mime,
            ];

            return response()->file($path, $headers);
        }
        abort(404);
    }

    private function speedWidth(int $speed)
    {
        if ($speed < 1000000) {
            return 1.0;
        }

        return (strlen((string) $speed) - 5) / 2.0;
    }

    private function speedColour(float $pct)
    {
        // For the maths below, the 5.1 is worked out as 255 / 50
        // (255 being the max colour value and 50 is the max of the $pct calcluation)
        if ($pct < 0) {
            // Black if we can't determine the percentage (link down or speed 0)
            return '#000000';
        } elseif ($pct < 50) {
            // 100% green and slowly increase the red until we get to yellow
            return sprintf('#%02XFF00', (int) (5.1 * $pct));
        } elseif ($pct < 100) {
            // 100% red and slowly remove green to go from yellow to red
            return sprintf('#FF%02X00', (int) (5.1 * (100.0 - $pct)));
        } elseif ($pct < 150) {
            // 100% red and slowly increase blue to go purple
            return sprintf('#FF00%02X', (int) (5.1 * ($pct - 100.0)));
        }

        // Default to purple for links over 150%
        return '#FF00FF';
    }

    private function snmpSpeed(string $speeds)
    {
        // Only succeed if the string startes with a number optionally followed by a unit
        if (preg_match('/^(\d+)([kMGTP])?/', $speeds, $matches)) {
            $speed = (int) $matches[1];
            if (count($matches) < 3) {
                return $speed;
            } elseif ($matches[2] == 'k') {
                $speed *= 1000;
            } elseif ($matches[2] == 'M') {
                $speed *= 1000000;
            } elseif ($matches[2] == 'G') {
                $speed *= 1000000000;
            } elseif ($matches[2] == 'T') {
                $speed *= 1000000000000;
            } elseif ($matches[2] == 'P') {
                $speed *= 1000000000000000;
            }

            return $speed;
        }

        return 0;
    }

    public function getData(Request $request)
    {
        $map = CustomMap::where('custom_map_id', '=', $request->map_id)
            ->hasAccess($request->user())
            ->with('nodes', 'nodes.device', 'nodes.linked_map', 'edges', 'edges.port', 'edges.port.device')
            ->first();

        if (! $map) {
            abort(404);
        }
        $edges = [];
        $nodes = [];

        foreach ($map->edges as $edge) {
            $edgeid = $edge->custom_map_edge_id;
            $edges[$edgeid] = [
                'custom_map_edge_id' => $edge->custom_map_edge_id,
                'custom_map_node1_id' => $edge->custom_map_node1_id,
                'custom_map_node2_id' => $edge->custom_map_node2_id,
                'port_id' => $edge->port_id,
                'reverse' => $edge->reverse,
                'style' => $edge->style,
                'showpct' => $edge->showpct,
                'text_face' => $edge->text_face,
                'text_size' => $edge->text_size,
                'text_colour' => $edge->text_colour,
                'mid_x' => $edge->mid_x,
                'mid_y' => $edge->mid_y,
            ];
            if ($edge->port) {
                $edges[$edgeid]['device_id'] = $edge->port->device_id;
                $edges[$edgeid]['port_name'] = $edge->port->device->displayName() . ' - ' . $edge->port->getLabel();
                $edges[$edgeid]['port_info'] = Url::portLink($edge->port, null, null, false, true);

                // Work out speed to and from
                $speedto = 0;
                $speedfrom = 0;
                $rateto = 0;
                $ratefrom = 0;

                // Try to interpret the SNMP speeds
                if ($edge->port->port_descr_speed) {
                    $speed_parts = explode('/', $edge->port->port_descr_speed, 2);

                    if (count($speed_parts) == 1) {
                        $speedto = $this->snmpSpeed($speed_parts[0]);
                        $speedfrom = $speedto;
                    } elseif ($edge->reverse) {
                        $speedto = $this->snmpSpeed($speed_parts[1]);
                        $speedfrom = $this->snmpSpeed($speed_parts[0]);
                    } else {
                        $speedto = $this->snmpSpeed($speed_parts[0]);
                        $speedfrom = $this->snmpSpeed($speed_parts[1]);
                    }
                    if ($speedto == 0 || $speedfrom == 0) {
                        $speedto = 0;
                        $speedfrom = 0;
                    }
                }

                // If we did not get a speed from the snmp desc, use the deteced speed
                if ($speedto == 0 && $edge->port->ifSpeed) {
                    $speedto = $edge->port->ifSpeed;
                    $speedfrom = $edge->port->ifSpeed;
                }

                // Get the to/from rates
                if ($edge->reverse) {
                    $ratefrom = $edge->port->ifInOctets_rate * 8;
                    $rateto = $edge->port->ifOutOctets_rate * 8;
                } else {
                    $ratefrom = $edge->port->ifOutOctets_rate * 8;
                    $rateto = $edge->port->ifInOctets_rate * 8;
                }

                if ($speedto == 0) {
                    $edges[$edgeid]['port_topct'] = -1.0;
                    $edges[$edgeid]['port_frompct'] = -1.0;
                } else {
                    $edges[$edgeid]['port_topct'] = round($rateto / $speedto * 100.0, 2);
                    $edges[$edgeid]['port_frompct'] = round($ratefrom / $speedfrom * 100.0, 2);
                }
                if ($edge->port->ifOperStatus != 'up') {
                    // If the port is not online, show the same as speed unknown
                    $edges[$edgeid]['colour_to'] = $this->speedColour(-1.0);
                    $edges[$edgeid]['colour_from'] = $this->speedColour(-1.0);
                } else {
                    $edges[$edgeid]['colour_to'] = $this->speedColour($edges[$edgeid]['port_topct']);
                    $edges[$edgeid]['colour_from'] = $this->speedColour($edges[$edgeid]['port_frompct']);
                }
                $edges[$edgeid]['width_to'] = $this->speedWidth($speedto);
                $edges[$edgeid]['width_from'] = $this->speedWidth($speedfrom);
            }
        }

        foreach ($map->nodes as $node) {
            $nodeid = $node->custom_map_node_id;
            $nodes[$nodeid] = [
                'custom_map_node_id' => $node->custom_map_node_id,
                'device_id' => $node->device_id,
                'linked_map_id' => $node->linked_custom_map_id,
                'linked_map_name' => $node->linked_map ? $node->linked_map->name : null,
                'label' => $node->label,
                'style' => $node->style,
                'icon' => $node->icon,
                'image' => $node->image,
                'size' => $node->size,
                'border_width' => $node->border_width,
                'text_face' => $node->text_face,
                'text_size' => $node->text_size,
                'text_colour' => $node->text_colour,
                'colour_bg' => $node->colour_bg,
                'colour_bdr' => $node->colour_bdr,
                'colour_bg_view' => $node->colour_bg,
                'colour_bdr_view' => $node->colour_bdr,
                'x_pos' => $node->x_pos,
                'y_pos' => $node->y_pos,
            ];
            if ($node->device) {
                $nodes[$nodeid]['device_name'] = $node->device->hostname . '(' . $node->device->sysName . ')';
                $nodes[$nodeid]['device_image'] = $node->device->icon;
                $nodes[$nodeid]['device_info'] = Url::deviceLink($node->device, null, [], 0, 0, 0, 0);

                if ($node->device->disabled) {
                    $device_style = $this->nodeDisabledStyle();
                } elseif (! $node->device->status) {
                    $device_style = $this->nodeDownStyle();
                } else {
                    $device_style = $this->nodeUpStyle();
                }

                if ($device_style['background']) {
                    $nodes[$nodeid]['colour_bg_view'] = $device_style['background'];
                }

                if ($device_style['border']) {
                    $nodes[$nodeid]['colour_bdr_view'] = $device_style['border'];
                }
            }
        }

        return response()->json(['nodes' => $nodes, 'edges' => $edges]);
    }

    public function view(Request $request)
    {
        $map = CustomMap::where('custom_map_id', '=', $request->map_id)
            ->hasAccess($request->user())
            ->first();

        if (! $map) {
            abort(404);
        }

        $name = $map->name;
        $newedge_conf = json_decode($map->newedgeconfig, true);
        $newnode_conf = json_decode($map->newnodeconfig, true);
        $map_conf = json_decode($map->options, true);
        $map_conf['width'] = $map->width;
        $map_conf['height'] = $map->height;
        $background = $map->background_suffix ? true : false;

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

        return view('map.custom-view', $data);
    }

    /**
     * Get a list of all available node images with a label.
     */
    private function listNodeImages(): array
    {
        $images = [];
        $image_translations = __('map.custom.edit.node.image_options');

        foreach (Storage::disk('base')->files('html/images/custommap/icons') as $image) {
            if (in_array(strtolower(pathinfo($image, PATHINFO_EXTENSION)), ['svg', 'png', 'jpg'])) {
                $file = pathinfo($image, PATHINFO_BASENAME);
                $filename = pathinfo($image, PATHINFO_FILENAME);

                $images[$file] = $image_translations[$filename] ?? ucwords(str_replace(['-', '_'], [' - ', ' '], $filename));
            }
        }

        return $images;
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

        if (is_null($request->map_id)) {
            $data['maps'] = CustomMap::orderBy('name')->get(['custom_map_id', 'name']);

            return view('map.custom-edit-select', $data);
        } elseif ($request->map_id == 0) {
            $data['name'] = 'New Map';
            $data['node_align'] = 10;
            $data['map_conf'] = [
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
            ];
            $data['background'] = null;

            return view('map.custom-new', $data);
        } else {
            $map = CustomMap::find($request->map_id);
            if (! $map) {
                abort(404);
            }

            $data['images'] = $this->listNodeImages();
            $data['maps'] = CustomMap::orderBy('name')->where('custom_map_id', '<>', $request->map_id)->get(['custom_map_id', 'name']);
            $data['name'] = $map->name;
            $data['node_align'] = $map->node_align;
            $data['newedge_conf'] = json_decode($map->newedgeconfig, true);
            $data['newnode_conf'] = json_decode($map->newnodeconfig, true);
            $data['map_conf'] = json_decode($map->options, true);
            $data['map_conf']['width'] = $map->width;
            $data['map_conf']['height'] = $map->height;
            // Override some settings for the editor
            $data['map_conf']['interaction'] = ['dragNodes' => true, 'dragView' => false, 'zoomView' => false];
            $data['map_conf']['manipulation'] = ['enabled' => true, 'initiallyActive' => true];
            $data['map_conf']['physics'] = ['enabled' => false];
            $data['background'] = $map->background_suffix ? true : false;
        }

        return view('map.custom-edit', $data);
    }

    public function save(Request $request)
    {
        if (! $request->user()->isAdmin()) {
            return response('Insufficient privileges');
        }

        $errors = [];

        $map = CustomMap::where('custom_map_id', '=', $request->map_id)->with('nodes', 'edges')->first();
        if (! $map) {
            abort(404);
        }

        $newnodeconf = json_decode($request->post('newnodeconf'));
        if (! $newnodeconf) {
            array_push($errors, 'New node config is not valid JSON');
        }

        $newedgeconf = json_decode($request->post('newedgeconf'));
        if (! $newedgeconf) {
            array_push($errors, 'New edge config is not valid JSON');
        }

        $nodes = json_decode($request->nodes);
        if (! $nodes) {
            array_push($errors, 'Node list is not valid JSON');
        }

        $edges = json_decode($request->edges);
        if (! $edges) {
            array_push($errors, 'Edge list is not valid JSON');
        }

        if (! count($errors)) {
            DB::transaction(function () use ($map, $request, $nodes, $edges) {
                $dbnodes = $map->nodes->keyBy('custom_map_node_id')->all();
                $dbedges = $map->edges->keyBy('custom_map_edge_id')->all();

                $nodesProcessed = [];
                $edgesProcessed = [];

                $newNodes = [];

                $map->newnodeconfig = $request->post('newnodeconf');
                $map->newedgeconfig = $request->post('newedgeconf');
                $map->save();

                foreach ($nodes as $nodeid => $node) {
                    if (strpos($nodeid, 'new') === 0) {
                        $dbnode = new CustomMapNode;
                        $dbnode->map()->associate($map);
                    } else {
                        $dbnode = $dbnodes[$nodeid];
                        if (! $dbnode) {
                            Log::error('Could not find existing node for node id ' . $nodeid);
                            abort(404);
                        }
                    }
                    $dbnode->device_id = is_numeric($node->title) ? $node->title : null;
                    $dbnode->linked_custom_map_id = str_starts_with($node->title, 'map:') ? (int) str_replace('map:', '', $node->title) : null;
                    $dbnode->label = $node->label;
                    $dbnode->style = $node->shape;
                    $dbnode->icon = $node->icon;
                    $dbnode->image = property_exists($node->image, 'unselected') ? $node->image->unselected : '';
                    $dbnode->size = $node->size;
                    $dbnode->text_face = $node->font->face;
                    $dbnode->text_size = $node->font->size;
                    $dbnode->text_colour = $node->font->color;
                    $dbnode->colour_bg = (array) $node->color ? $node->color->background : null;
                    $dbnode->colour_bdr = (array) $node->color ? $node->color->border : null;
                    $dbnode->border_width = $node->borderWidth;
                    $dbnode->x_pos = intval($node->x);
                    $dbnode->y_pos = intval($node->y);

                    $dbnode->save();
                    $nodesProcessed[$dbnode->custom_map_node_id] = true;
                    $newNodes[$nodeid] = $dbnode;
                }
                foreach ($edges as $edgeid => $edge) {
                    if (strpos($edgeid, 'new') === 0) {
                        $dbedge = new CustomMapEdge;
                        $dbedge->map()->associate($map);
                    } else {
                        $dbedge = $dbedges[$edgeid];
                        if (! $dbedge) {
                            Log::error('Could not find existing edge for edge id ' . $edgeid);
                            abort(404);
                        }
                    }
                    $dbedge->custom_map_node1_id = strpos($edge->from, 'new') == 0 ? $newNodes[$edge->from]->custom_map_node_id : $edge->from;
                    $dbedge->custom_map_node2_id = strpos($edge->to, 'new') == 0 ? $newNodes[$edge->to]->custom_map_node_id : $edge->to;
                    $dbedge->port_id = $edge->port_id ? $edge->port_id : null;
                    $dbedge->reverse = $edge->reverse;
                    $dbedge->showpct = $edge->showpct;
                    $dbedge->style = $edge->style;
                    $dbedge->text_face = $edge->text_face;
                    $dbedge->text_size = $edge->text_size;
                    $dbedge->text_colour = $edge->text_colour;
                    $dbedge->mid_x = intval($edge->mid_x);
                    $dbedge->mid_y = intval($edge->mid_y);

                    $dbedge->save();
                    $edgesProcessed[$dbedge->custom_map_edge_id] = true;
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
        if (! $request->user()->isAdmin()) {
            return response('Insufficient privileges');
        }

        $errors = [];

        $map_id = $request->map_id;
        $name = $request->post('name');
        $width = $request->post('width');
        $height = $request->post('height');
        $node_align = $request->post('node_align');
        $bgclear = $request->post('bgclear') == 'true' ? true : false;
        $bgnewimage = $request->post('bgimage');

        if (! preg_match('/^(\d+)(px|%)$/', $width, $matches)) {
            array_push($errors, 'Width must be a number followed by px or %');
        } elseif ($matches[2] == 'px' && $matches[1] < 200) {
            array_push($errors, 'Width in pixels must be at least 200');
        } elseif ($matches[2] == '%' && ($matches[1] < 10 || $matches[1] > 100)) {
            array_push($errors, 'Width percent must be between 10 and 100');
        }

        if (! preg_match('/^(\d+)(px|%)$/', $height, $matches)) {
            array_push($errors, 'Height must be a number followed by px or %');
        } elseif ($matches[2] == 'px' && $matches[1] < 200) {
            array_push($errors, 'Height in pixels must be at least 200');
        } elseif ($matches[2] == '%' && ($matches[1] < 10 || $matches[1] > 100)) {
            array_push($errors, 'Height percent must be between 10 and 100');
        }

        if (! $name) {
            array_push($errors, 'Name must be supplied');
        }

        if ($bgnewimage) {
            $request->validate(['bgimage' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048']);
        }

        $background = false;
        if (! $errors) {
            if (! $map_id) {
                $map = new CustomMap;
                $map->options = json_encode($this->default_map_options);
                $map->newnodeconfig = json_encode($this->default_node_conf);
                $map->newedgeconfig = json_encode($this->default_edge_conf);
                $map->background_version = 0;
            } else {
                $map = CustomMap::find($map_id);
            }

            $map->name = $name;
            $map->width = $width;
            $map->height = $height;
            $map->node_align = $node_align;
            $map->save();
            if (! $map_id) {
                $map_id = $map->custom_map_id;
            }

            if ($request->bgimage) {
                $map->background_suffix = $request->bgimage->extension();
                if (! $map->background) {
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
                if ($map->background) {
                    $map->background->delete();
                }
                $map->background_suffix = null;
                $map->save();
                $map->refresh();
            }
            $background = $map->background_suffix ? true : false;
        }

        return response()->json([
            'id' => $map_id,
            'width' => $map->width ?? '',
            'height' => $map->height ?? '',
            'bgimage' => $background,
            'errors' => $errors
        ]);
    }
}
