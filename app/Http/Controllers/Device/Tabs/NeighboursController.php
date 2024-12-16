<?php
/**
 * NeighboursController.php
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
 *
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs;

use App\Models\Device;
use App\Models\Link;
use Illuminate\Http\Request;
use LibreNMS\Config;
use LibreNMS\Interfaces\UI\DeviceTab;
use LibreNMS\Util\Url;

class NeighboursController implements DeviceTab
{
    public function visible(Device $device): bool
    {
        return Link::where('local_device_id', $device->device_id)->exists();
    }

    public function slug(): string
    {
        return 'neighbours';
    }

    public function icon(): string
    {
        return 'fa-sitemap';
    }

    public function name(): string
    {
        return __('Neighbours');
    }

    public function data(Device $device, Request $request): array
    {
        $selection = Url::parseOptions('selection', 'list');

        $devices[$device->device_id] = [
            'url' => Url::deviceLink($device, null, [], 0, 0, 0, 1),
            'hw' => $device->hardware,
            'name' => $device->shortDisplayName(),
        ];

        if ($selection == 'list') {
            $linksQuery = $device->links()->with('port', 'remoteDevice', 'remotePort');

            $links = $linksQuery->get()->sortBy('port.ifName');
        } else {
            $links = [];
        }

        return [
            'selections' => [
                'list' => [
                    'text' => 'List',
                    'link' => route('device', ['device' => $device, 'tab' => 'neighbours', 'vars' => 'selection=list']),
                ],
                'map' => [
                    'text' => 'Map',
                    'link' => route('device', ['device' => $device, 'tab' => 'neighbours', 'vars' => 'selection=map']),
                ],
            ],
            'selection' => $selection,
            'device' => $device,
            'links' => $links,
            'link_types' => Config::get('network_map_items', ['xdp', 'mac']),
            'visoptions' => Config::get('network_map_vis_options'),
        ];
    }
}
