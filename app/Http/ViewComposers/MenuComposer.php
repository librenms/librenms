<?php
/**
 * Menu.php
 *
 * Builds data for LibreNMS menu
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\ViewComposers;

use App\Models\AlertRule;
use App\Models\BgpPeer;
use App\Models\Dashboard;
use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Package;
use App\Models\User;
use App\Models\UserPref;
use App\Models\Vminfo;
use App\Models\WirelessSensor;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use LibreNMS\Config;
use LibreNMS\Util\ObjectCache;

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
        $user = Auth::user();
        $site_style = Config::get('applied_site_style');

        //global Settings
        $vars['hide_dashboard_editor'] = UserPref::getPref($user, 'hide_dashboard_editor');
        // end global Settings

        //TODO: should be handled via CSS Themes
        $vars['navbar'] = in_array($site_style, ['mono']) ? 'navbar-inverse' : '';

        $vars['project_name'] = Config::get('project_name', 'LibreNMS');
        $vars['title_image'] = Config::get('title_image', "images/librenms_logo_$site_style.svg");

        //Dashboards
        $vars['dashboards'] = Dashboard::select('dashboard_id', 'dashboard_name')->allAvailable($user)->orderBy('dashboard_name')->get();

        // Device menu
        $vars['device_groups'] = DeviceGroup::hasAccess($user)->orderBy('name')->get(['device_groups.id', 'name', 'desc']);
        $vars['package_count'] = Package::hasAccess($user)->count();

        $vars['device_types'] = Device::hasAccess($user)->select('type')->distinct()->where('type', '!=', '')->orderBy('type')->pluck('type');

        $vars['locations'] = (Config::get('show_locations') && Config::get('show_locations_dropdown')) ?
            Location::hasAccess($user)->where('location', '!=', '')->orderBy('location')->get(['location', 'id']) :
            collect();
        $vars['show_vmwinfo'] = Vminfo::hasAccess($user)->exists();

        // Service menu
        if (Config::get('show_services')) {
            $vars['service_counts'] = ObjectCache::serviceCounts(['warning', 'critical']);
        }

        // Port menu
        $vars['port_counts'] = ObjectCache::portCounts(['errored', 'ignored', 'deleted', 'shutdown', 'down']);
        $vars['port_counts']['pseudowire'] = Config::get('enable_pseudowires') ? ObjectCache::portCounts(['pseudowire'])['pseudowire'] : 0;

        $vars['port_counts']['alerted'] = 0; // not actually supported on old...

        $custom_descr = [];
        foreach ((array) Config::get('custom_descr', []) as $descr) {
            $custom_descr_name = is_array($descr) ? $descr[0] : $descr;
            if (empty($custom_descr_name)) {
                continue;
            }
            $custom_descr[] = ['name' => $custom_descr_name,
                'icon' => is_array($descr) ? $descr[1] : 'fa-connectdevelop',
            ];
        }
        $vars['custom_port_descr'] = collect($custom_descr)->filter();
        $vars['port_groups_exist'] = Config::get('int_customers') ||
            Config::get('int_transit') ||
            Config::get('int_peering') ||
            Config::get('int_core') ||
            Config::get('int_l2tp') ||
            $vars['custom_port_descr']->isNotEmpty();

        // Sensor menu
        $vars['sensor_menu'] = ObjectCache::sensors();

        // Wireless menu
        $wireless_menu_order = array_keys(\LibreNMS\Device\WirelessSensor::getTypes());
        $vars['wireless_menu'] = WirelessSensor::hasAccess($user)
            ->groupBy('sensor_class')
            ->get(['sensor_class'])
            ->sortBy(function ($wireless_sensor) use ($wireless_menu_order) {
                $pos = array_search($wireless_sensor->sensor_class, $wireless_menu_order);

                return $pos === false ? 100 : $pos; // unknown at bottom
            });

        // Application menu
        $vars['app_menu'] = ObjectCache::applications();

        // Routing menu
        // FIXME queries use relationships to user
        $routing_menu = [];
        if ($user->hasGlobalRead()) {
            $routing_count = ObjectCache::routing();

            if ($routing_count['vrf']) {
                $routing_menu[] = [
                    [
                        'url' => 'vrf',
                        'icon' => 'arrows',
                        'text' => 'VRFs',
                    ],
                ];
            }

            if ($routing_count['mpls']) {
                $routing_menu[] = [
                    [
                        'url' => 'mpls',
                        'icon' => 'tag',
                        'text' => 'MPLS',
                    ],
                ];
            }

            if ($routing_count['ospf']) {
                $routing_menu[] = [
                    [
                        'url' => 'ospf',
                        'icon' => 'circle-o-notch fa-rotate-180',
                        'text' => 'OSPF Devices',
                    ],
                ];
            }

            if ($routing_count['isis']) {
                $routing_menu[] = [
                    [
                        'url' => 'isis',
                        'icon' => 'arrows-alt',
                        'text' => 'ISIS Adjacencies',
                    ],
                ];
            }

            if ($routing_count['cisco-otv']) {
                $routing_menu[] = [
                    [
                        'url' => 'cisco-otv',
                        'icon' => 'exchange',
                        'text' => 'Cisco OTV',
                    ],
                ];
            }

            if ($routing_count['bgp']) {
                $vars['show_peeringdb'] = Config::get('peeringdb.enabled', false);
                $vars['bgp_alerts'] = BgpPeer::hasAccess($user)->inAlarm()->count();
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
            } else {
                $vars['show_peeringdb'] = false;
                $vars['bgp_alerts'] = [];
            }

            if ($routing_count['cef']) {
                $routing_menu[] = [
                    [
                        'url' => 'cef',
                        'icon' => 'exchange',
                        'text' => 'Cisco CEF',
                    ],
                ];
            }
        }
        $vars['routing_menu'] = $routing_menu;

        // Alert menu
        $alert_status = AlertRule::select('severity')
            ->isActive()
            ->hasAccess($user)
            ->leftJoin('devices', 'alerts.device_id', '=', 'devices.device_id')
            ->where('devices.disabled', '=', '0')
            ->where('devices.ignore', '=', '0')
            ->groupBy('severity')
            ->pluck('severity');

        if ($alert_status->contains('critical')) {
            $vars['alert_menu_class'] = 'danger';
        } elseif ($alert_status->contains('warning')) {
            $vars['alert_menu_class'] = 'warning';
        } else {
            $vars['alert_menu_class'] = 'success';
        }

        // User menu
        $vars['notification_count'] = Notification::isSticky()
            ->orWhere(function ($query) use ($user) {
                $query->isUnread($user);
            })->count();

        // Poller Settings
        $vars['poller_clusters'] = \App\Models\PollerCluster::exists();

        // Search bar
        $vars['typeahead_limit'] = Config::get('webui.global_search_result_limit');

        $view->with($vars);
    }
}
