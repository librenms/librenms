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

use App\Plugins\Hooks\PageHook;

// this page will be shown when the user clicks on the plugin from the plugins menu.
// This allows you to output a full screen of whatever you want to the user
class Page extends PageHook
{
    // point to the view for your plugin's settings
    // this is the default name so you can create the blade file as in this plugin
    // by ommitting the variable, or point to another one

//    public string $view = 'resources.views.page';

    // The authorize method will determine if the user has access to this page.
    // if you want all users to be able to access this page simple return true
    public function authorize(\App\Models\User $user): bool
    {
        // you can check user's roles like this:
//        return $user->can('admin');

        // or use whatever you like
//        return \Carbon\Carbon::now()->dayOfWeek == Carbon::THURSDAY; // only allowed access on Thursdays!

        return true; // allow every logged in user to access
    }

    // override the data function to add additional data to be accessed in the view
    // default just passes the stored data through
    // inside the blade, all variables will be named based on the key in the returned array
    public function data(): array
    {
        // run any calculations here
        $username = auth()->user()->username;

        return [
            'something' => 'this is a variable and can be accessed with {{ $something }}',
            'hello' => 'Hello: ' . $username,
        ];
    }
}
