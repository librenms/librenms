<?php
/*
 * ExampleSettingsPlugin.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Plugins\ExamplePlugin;

use App\Plugins\Hooks\DeviceOverviewHook;

class DeviceOverview extends DeviceOverviewHook
{
    // point to the view for your plugin's settings
    // this is the default name so you can create the blade file as in this plugin
    // by ommitting the variable, or point to another one

//    public string $view = 'resources.views.device-overview';

    public function authorize(\App\Models\User $user, \App\Models\Device $device): bool
    {
        // In this example, we check if the user has a custom role/permission and if it is member of any device groups
//        return $user->can('view-extra-port-info') && $device->has('groups');

        return true;
    }

    // override the data function to add additional data to be accessed in the view
    // title is a required attribute and will be shown above your returned html from your blade file
    // inside the blade, all variables will be named based on the key in the returned array
    public function data(\App\Models\Device $device): array
    {
        // here we pass a title string, url to notes, and the device to the blade view for display

        return [
            'title' => 'Example Plugin: Device Notes',
            'device' => $device,
            'url' => url('device/' . $device->device_id . '/notes'),
        ];
    }
}
