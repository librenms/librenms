<?php
/**
 * Menu.php
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

namespace App\Http\ViewComposers;

use App\Models\Application;
use App\Models\BgpPeer;
use App\Models\CefSwitching;
use App\Models\Component;
use App\Models\DeviceGroup;
use App\Models\OspfInstance;
use App\Models\Package;
use App\Models\Sensor;
use App\Models\Service;
use App\Models\User;
use App\Models\Vrf;
use App\Models\WirelessSensor;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use LibreNMS\Config;


class MenuComposer
{
    /**
     * Bind data to the view.
     *
     * @param  View $view
     * @return void
     */
    public function compose(View $view)
    {
        $vars = [];
        /** @var User $user */
        $user = auth()->user();

        $vars['navbar'] = in_array(Config::get('site_style'), ['mono', 'dark']) ? 'navbar-inverse' : '';

        if ($title_image = Config::get('title_image')) {
            $vars['title_image'] = '<img src="' . $title_image . '" /></a>';
        } else {
            $vars['title_image'] = Config::get('project_name', 'LibreNMS');
        }

        $vars['device_groups'] = DeviceGroup::select('id', 'name', 'desc')->get();
        $vars['package_count'] = Package::count();

        $vars['device_types'] = $user->devices()->select('type')->distinct()->get()->pluck('type');

        if (Config::get('show_locations') && Config::get('show_locations_dropdown')) {
            $vars['locations'] = $user->devices()->select('location')->distinct()->get()->pluck('location')->filter();
        } else {
            $vars['locations'] = [];
        }

        if (Config::get('show_services')) {
            $vars['service_status'] = Service::groupBy('service_status')
                ->select('service_status', DB::raw('count(*) as count'))
                ->whereIn('service_status', [1, 2])
                ->get()
                ->keyBy('service_status');

            $warning = $vars['service_status']->get(1);
            $vars['service_warning'] = $warning ? $warning->count : 0;
            $critical = $vars['service_status']->get(2);
            $vars['service_critical'] = $critical ? $critical->count : 0;
        }

        // Port menu
        // FIXME actual queries
        $vars['port_counts'] = [
            'count' => 5,
            'up' => 1,
            'down' => 1,
            'shutdown' => 1,
            'errored' => 1,
            'ignored' => 1,
            'deleted' => 1,
            'alerted' => 1, // not actually supported on old...
        ];

        // Sensor menu
        $sensor_menu = [];
        $sensor_classes = Sensor::select('sensor_class')->groupBy('sensor_class')->orderBy('sensor_class')->get();

        foreach ($sensor_classes as $sensor_model) {
            /** @var Sensor $sensor_model */
            $class = $sensor_model->sensor_class;
            if (in_array($class, ['fanspeed', 'humidity', 'temperature', 'signal'])) {
                // First group
                $group = 0;
            } elseif (in_array($class, ['current', 'frequency', 'power', 'voltage'])) {
                // Second group
                $group = 1;
            } else {
                // anything else
                $group = 2;
            }

            $sensor_menu[$group][] = $sensor_model;
        }
        $vars['sensor_menu'] = $sensor_menu;

        // Wireless Menu
        $wireless_menu_order = array_keys(\LibreNMS\Device\WirelessSensor::getTypes());
        $vars['wireless_menu'] = WirelessSensor::select('sensor_class')
            ->groupBy('sensor_class')
            ->get()
            ->sortBy(function ($wireless_sensor) use ($wireless_menu_order) {
                $pos = array_search($wireless_sensor->sensor_class, $wireless_menu_order);
                return $pos === false ? 100 : $pos; // unknown at bottom
            });

        // Application Menu
        if ($user->hasGlobalRead()) {
            $vars['app_menu'] = Application::select('app_type', 'app_instance')
                ->groupBy('app_type', 'app_instance')
                ->orderBy('app_type')
                ->get()
                ->groupBy('app_type');
        } else {
            $vars['app_menu'] = false;
        }

        // Routing Menu
        // FIXME queries use relationships to user
        $routing_menu = [];
        if ($user->hasGlobalRead()) {
            if (Vrf::count()) {
                $routing_menu[] = [['url' => 'vrf',
                    'icon' => 'arrows',
                    'text' => 'VRFs',]];
            }

            if (OspfInstance::count()) {
                $routing_menu[] = [[
                    'url' => 'ospf',
                    'icon' => 'circle-o-notch fa-rotate-180',
                    'text' => 'OSPF Devices',
                ]];
            }

            if (Component::where('type', 'Cisco-OTV')->count()) {
                $routing_menu[] = [[
                    'url' => 'cisco-otv',
                    'icon' => 'exchange',
                    'text' => 'Cisco OTV',
                ]];
            }

            if (BgpPeer::count()) {
                $vars['show_peeringdb'] = Config::get('peeringdb.enabled', false);
                $vars['bgp_alerts'] = BgpPeer::inAlarm()->count();
                $routing_menu[] = [
                    [
                        'url' => 'bgp/type=all/graph=NULL',
                        'icon' => 'circle-o',
                        'text' => 'BGP All Sessions',
                    ],
                    [
                        'url' => 'bgp/type=external/graph=NULL',
                        'icon' => 'external-link',
                        'text' => 'BGP External',
                    ],
                    [
                        'url' => 'bgp/type=internal/graph=NULL',
                        'icon' => 'external-link fa-rotate-180',
                        'text' => 'BGP Internal',
                    ],
                ];
            }

            if (CefSwitching::count()) {
                $routing_menu[] = [[
                    'url' => 'cef',
                    'icon' => 'exchange',
                    'text' => 'Cisco CEF',
                ]];
            }
        }
        $vars['routing_menu'] = $routing_menu;



        $view->with($vars);
    }
}
