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
use App\Models\Port;
use App\Models\Link;
use Illuminate\Http\Request;
use LibreNMS\Config;
use LibreNMS\Interfaces\UI\DeviceTab;
use LibreNMS\Interfaces\UI\DeviceTab;
use LibreNMS\Util\Url;

class NeighboursController implements DeviceTab
{
    public function visible(Device $device): bool
    {
        return Link::where('local_device_id', $device->device_id)
            ->orWhere('remote_device_id', $device->device_id)
            ->exists();
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

        $links = [];
        $devices = [];

        if ($selection == 'list') {
            $linkQuery = Port::select(
                \DB::raw('ports.*'),
                \DB::raw('l.remote_port_id'),
                \DB::raw('l.remote_hostname'),
                \DB::raw('l.remote_port'),
                \DB::raw('l.remote_platform'),
                \DB::raw('l.protocol'),
            )
                ->with('device')
                ->join('links as l', 'l.local_port_id', '=', 'ports.port_id')
                ->where('l.local_device_id', '=', $device->device_id)
                ->orderBy('ports.ifName');

            // Only show where user has access to both devices
            if (! \Auth::user()->hasGlobalRead()) {
                $linkQuery->whereIntegerInRaw('l.local_device_id', \Permissions::devicesForUser())
                    ->whereIntegerInRaw('l.remote_device_id', \Permissions::devicesForUser());
            }

            foreach ($linkQuery->get() as $port) {
                $row = json_decode(json_encode($port));

                if (! in_array($port->device->device_id, $devices)) {
                    $devices[$port->device->device_id] = [
                        'url'  => Url::deviceLink($port->device, null, [], 0, 0, 0, 1),
                        'hw'   => $port->device->hardware,
                        'name' => $port->device->shortDisplayName(),
                    ];
                }

                $rport = null;
                if ($row->remote_port_id) {
                    $rport = Port::where('port_id', '=', $port->remote_port_id)->with('device')->first();

                    if (! in_array($rport->device->device_id, $devices)) {
                        $devices[$rport->device->device_id] = [
                            'url'  => Url::deviceLink($rport->device, null, [], 0, 0, 0, 1),
                            'hw'   => $rport->device->hardware,
                            'name' => $rport->device->shortDisplayName(),
                        ];
                    }
                }

                $links[] = [
                    'local_url'       => Url::portLink($port, null, null, true, false),
                    'ldev_id'         => $port->device->device_id,
                    'local_portname'  => $port->ifAlias,
                    'remote_url'      => $rport ? Url::portLink($rport, null, null, true, false) : '',
                    'rdev_id'         => $rport ? $rport->device->device_id : null,
                    'rdev_name'       => $row->remote_hostname,
                    'rdev_platform'   => $port->remote_platform,
                    'remote_portname' => $rport ? $rport->ifAlias : $row->remote_port,
                    'protocol'        => strtoupper($row->protocol),
                ];
            }
        }

        return [
            'selections' => [
                'list' => [
                    'text' => 'List',
                    'link' => Url::deviceUrl($device, ['tab' => 'neighbours', 'selection' => 'list']),
                ],
                'map' => [
                    'text' => 'Map',
                    'link' => Url::deviceUrl($device, ['tab' => 'neighbours', 'selection' => 'map']),
                ],
            ],
            'selection'  => $selection,
            'device_id'  => $device->device_id,
            'devices'    => $devices,
            'links'      => $links,
            'link_types' => Config::get('network_map_items', ['xdp', 'mac']),
            'visoptions' => Config::get('network_map_vis_options'),
        ];
    }
}
