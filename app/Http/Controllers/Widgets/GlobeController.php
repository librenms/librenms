<?php
/**
 * GlobeController.php
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

use Illuminate\Http\Request;
use Illuminate\View\View;
use LibreNMS\Config;

class GlobeController extends WidgetController
{
    /**
     * @param Request $request
     * @return View
     */
    public function getView(Request $request)
    {
        $locations = array();

        $dbl = array();



        // Fetch regular locations
        if (LegacyAuth::user()->hasGlobalRead()) {
            $rows = dbFetchRows('SELECT location FROM devices AS D GROUP BY location ORDER BY location');
        } else {
            $rows = dbFetchRows('SELECT location FROM devices AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = ? GROUP BY location ORDER BY location', array(LegacyAuth::id()));
        }

        foreach ($rows as $row) {
            // Only add it as a location if it wasn't overridden (and not already there)
            if ($row['location'] != '') {
                if (!in_array($row['location'], $locations)) {
                    $dbl[] = $row['location'];
                }
            }
        }

        sort($dbl);


        foreach ($dbl as $location) {
            $location = mres($location);
            $devices = array();
            $devices_down = array();
            $devices_up = array();
            $count = 0;
            $down  = 0;
            foreach (dbFetchRows("SELECT devices.device_id,devices.hostname,devices.status FROM devices LEFT JOIN devices_attribs ON devices.device_id = devices_attribs.device_id WHERE ( devices.location = ? || ( devices_attribs.attrib_type = 'override_sysLocation_string' && devices_attribs.attrib_value = ? ) ) && devices.disabled = 0 && devices.ignore = 0 GROUP BY devices.hostname", array($location,$location)) as $device) {
                if ($config['frontpage_globe']['markers'] == 'devices' || empty($config['frontpage_globe']['markers'])) {
                    $devices[] = $device['hostname'];
                    $count++;
                    if ($device['status'] == "0") {
                        $down++;
                        $devices_down[] = $device['hostname']." DOWN";
                    } else {
                        $devices_up[] = $device;
                    }
                } elseif ($config['frontpage_globe']['markers'] == 'ports') {
                    foreach (dbFetchRows("SELECT ifName,ifOperStatus,ifAdminStatus FROM ports WHERE ports.device_id = ? && ports.ignore = 0 && ports.disabled = 0 && ports.deleted = 0", array($device['device_id'])) as $port) {
                        $count++;
                        if ($port['ifOperStatus'] == 'down' && $port['ifAdminStatus'] == 'up') {
                            $down++;
                            $devices_down[] = $device['hostname']."/".$port['ifName']." DOWN";
                        } else {
                            $devices_up[] = $port;
                        }
                    }
                }
            }
            $pdown = ($down / $count)*100;
            if ($config['frontpage_globe']['markers'] == 'devices' || empty($config['frontpage_globe']['markers'])) {
                $devices_down = array_merge(array(count($devices_up). " Devices OK"), $devices_down);
            } elseif ($config['frontpage_globe']['markers'] == 'ports') {
                $devices_down = array_merge(array(count($devices_up). " Ports OK"), $devices_down);
            }
            $locations[] = "            ['".$location."', ".$pdown.", ".$count.", '".implode(",<br/> ", $devices_down)."']";
        }
        $temp_output .= implode(",\n", $locations);

        $map_world = Config::get('frontpage_globe.region', 'world');
        $map_countries = Config::get('frontpage_globe.resolution', 'countries');


        return view('widgets.globe', compact('locations', 'map_world', 'map_countries'));
    }
}
