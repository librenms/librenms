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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Widgets;

use App\Models\Port;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LibreNMS\Util\Url;

class GraphController extends WidgetController
{
    protected $title = 'Graph';

    public function getSettingsView(Request $request)
    {
        return view('widgets.settings.graph', $this->getSettings());
    }

    /**
     * @param Request $request
     * @return View
     */
    public function getView(Request $request)
    {
//        array:11 [
//          "title" => ""
//          "graph_type" => "port_bits"
//          "graph_range" => "onehour"
//          "graph_device" => ""
//          "graph_port" => "{"name":"enp4s0","description":"Broadcom Limited NetLink BCM57781 Gigabit Ethernet PCIe","hostname":"amorbis","port_id":2}"
//          "graph_application" => ""
//          "graph_munin" => ""
//          "graph_custom" => ""
//          "graph_manual" => ""
//          "graph_bill" => ""
//          "id" => "9"
//        ]

        $settings = $this->getSettings();
        $dimensions = $request->get('dimensions');

        $port_data = json_decode($settings['graph_port'], true);
        $port = Port::find(is_array($port_data) ? $port_data['port_id'] : $settings['graph_port']);

        $graph_array = [
            'type' => $settings['graph_type'] ?: 'port_bits',
            'legend' => 'yes',
            'width' => $dimensions['x'],
            'height' => $dimensions['y'],
            'to' => Carbon::now()->timestamp,
            'from' => Carbon::now()->subDay()->timestamp, // graph_range
            'id' => $port->port_id,
        ];
        $graph = Url::graphTag($graph_array);
        $graph_image = Url::portLink($port, $graph);

        return view('widgets.graph', compact('graph_image'));
    }
}
