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
use App\Models\CustomMapEdge;
use App\Models\CustomMapNode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LibreNMS\Config;
use LibreNMS\Util\Url;

class CustomMapDataController extends Controller
{
    public function get(Request $request, CustomMap $map): JsonResponse
    {
        $this->authorize('view', $map);

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
                'showbps' => $edge->showbps,
                'label' => $edge->label,
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
                $edges[$edgeid]['port_tobps'] = $this->rateString($rateto);
                $edges[$edgeid]['port_frombps'] = $this->rateString($ratefrom);
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

    public function save(Request $request, CustomMap $map): JsonResponse
    {
        $this->authorize('update', $map);

        $data = $this->validate($request, [
            'newnodeconf' => 'array',
            'newedgeconf' => 'array',
            'nodes' => 'array',
            'edges' => 'array',
            'legend_x' => 'integer',
            'legend_y' => 'integer',
        ]);

        $map->load(['nodes', 'edges']);

        DB::transaction(function () use ($map, $data) {
            if ($map->legend_x != $data['legend_x'] || $map->legend_y != $data['legend_y']) {
                $map->legend_x = $data['legend_x'];
                $map->legend_y = $data['legend_y'];

                $map->save();
            }

            $dbnodes = $map->nodes->keyBy('custom_map_node_id')->all();
            $dbedges = $map->edges->keyBy('custom_map_edge_id')->all();

            $nodesProcessed = [];
            $edgesProcessed = [];

            $newNodes = [];

            $map->newnodeconfig = $data['newnodeconf'];
            $map->newedgeconfig = $data['newedgeconf'];
            $map->save();

            foreach ($data['nodes'] as $nodeid => $node) {
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
                $dbnode->device_id = is_numeric($node['title']) ? $node['title'] : null;
                $dbnode->linked_custom_map_id = str_starts_with($node['title'], 'map:') ? (int) str_replace('map:', '', $node['title']) : null;
                $dbnode->label = $node['label'];
                $dbnode->style = $node['shape'];
                $dbnode->icon = $node['icon'];
                $dbnode->image = $node['image']['unselected'] ?? '';
                $dbnode->size = $node['size'];
                $dbnode->text_face = $node['font']['face'];
                $dbnode->text_size = $node['font']['size'];
                $dbnode->text_colour = $node['font']['color'];
                $dbnode->colour_bg = $node['color']['background'] ?? null;
                $dbnode->colour_bdr = $node['color']['border'] ?? null;
                $dbnode->border_width = $node['borderWidth'];
                $dbnode->x_pos = intval($node['x']);
                $dbnode->y_pos = intval($node['y']);

                $dbnode->save();
                $nodesProcessed[$dbnode->custom_map_node_id] = true;
                $newNodes[$nodeid] = $dbnode;
            }
            foreach ($data['edges'] as $edgeid => $edge) {
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
                $dbedge->custom_map_node1_id = strpos($edge['from'], 'new') == 0 ? $newNodes[$edge['from']]->custom_map_node_id : $edge['from'];
                $dbedge->custom_map_node2_id = strpos($edge['to'], 'new') == 0 ? $newNodes[$edge['to']]->custom_map_node_id : $edge['to'];
                $dbedge->port_id = $edge['port_id'] ? $edge['port_id'] : null;
                $dbedge->reverse = filter_var($edge['reverse'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                $dbedge->showpct = filter_var($edge['showpct'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                $dbedge->showbps = filter_var($edge['showbps'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                $dbedge->label = $edge['label'] ? $edge['label'] : '';
                $dbedge->style = $edge['style'];
                $dbedge->text_face = $edge['text_face'];
                $dbedge->text_size = $edge['text_size'];
                $dbedge->text_colour = $edge['text_colour'];
                $dbedge->mid_x = intval($edge['mid_x']);
                $dbedge->mid_y = intval($edge['mid_y']);

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

        return response()->json(['id' => $map->custom_map_id]);
    }

    private function rateString(int $rate): string
    {
        if ($rate < 1000) {
            return $rate . ' bps';
        } elseif ($rate < 1000000) {
            return intval($rate / 1000) . ' kbps';
        } elseif ($rate < 1000000000) {
            return intval($rate / 1000000) . ' Mbps';
        } elseif ($rate < 1000000000000) {
            return intval($rate / 1000000000) . ' Gbps';
        } elseif ($rate < 1000000000000000) {
            return intval($rate / 1000000000000) . ' Tbps';
        } else {
            return intval($rate / 1000000000000000) . ' Pbps';
        }
    }

    private function snmpSpeed(string $speeds): int
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

    private function speedColour(float $pct): string
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

    private function speedWidth(int $speed): float
    {
        if ($speed < 1000000) {
            return 1.0;
        }

        return (strlen((string) $speed) - 5) / 2.0;
    }

    protected function nodeDisabledStyle(): array
    {
        return [
            'border' => Config::get('network_map_legend.di.border'),
            'background' => Config::get('network_map_legend.di.node'),
        ];
    }

    protected function nodeDownStyle(): array
    {
        return [
            'border' => Config::get('network_map_legend.dn.border'),
            'background' => Config::get('network_map_legend.dn.node'),
        ];
    }

    protected function nodeUpStyle(): array
    {
        return [
            'border' => null,
            'background' => null,
        ];
    }
}
