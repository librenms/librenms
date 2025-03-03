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

use App\Plugins\Hooks\SettingsHook;

// In the plugins admin page, there will be a settings button if you implement this hook
// To save settings in your settings page, you should have a form that returns all variables
// you want to save in the database.
class Settings extends SettingsHook
{
    // point to the view for your plugin's settings
    // this is the default name so you can create the blade file as in this plugin
    // by ommitting the variable, or point to another one

//    public string $view = 'resources.views.settings';

    // override the data function to add additional data to be accessed in the view
    // default just passes the stored data through
    // inside the blade, all variables will be named based on the key in the returned array
    public function data(array $settings = []): array
    {
        // run any calculations here
        $total = array_sum([1, 2, 3, 4]);

        return [
            'settings' => $settings, // this is an array of all the settings stored in the database
            'something' => 'this is a variable and can be accessed with {{ $something }}',
            'total' => $total,
        ];
    }
}
