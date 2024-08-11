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
 * @copyright  2024 PipoCanaja
 * @author     PipoCanaja <pipocanaja@gmail.com>
 */

namespace App\Plugins\ExamplePlugin;

use App\Plugins\Hooks\ResourceAccessorHook;
use App\Models\User;
use App\Models\Device;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

// This ResourceHook is called from a Controller, and allows any plugin to publish files, images, etc, to be downloaded

class ResourceAccessor extends ResourceAccessorHook
{
    public function authorize(User $user): bool
    {
        // In this example, we check if the user has a custom role/permission 
        // return $user->can('download-reports') 
        return true;
    }

    // process the request, and returns
    //   - A response() as defined here : https://laravel.com/docs/responses
    //   - An array with the following syntax:
    //      ['action' => 'abort', 'abort_type' => 403], more details here : https://laravel.com/docs/errors#http-exceptions

    public function processRequest(Request $request, string $path, array $settings)
    {
        // you are free to change the path here. You can also generate files on the fly.
        $full_path = base_path('/app/Plugins/ExamplePlugin/files/' . $path);

        // if you use a real file, better check if it exists and is readable, and return the proper abort instruction for the framework
        if (! is_file($full_path)) {
            return (['action' => 'abort', 'abort_type' => 404]);
        }
        if (! is_readable($full_path)) {
            return (['action' => 'abort', 'abort_type' => 403]);
        }

        $result = response()->file($full_path); // to return an image or a file to be displayed inline
        //$result = response()->download($full_path); // to force the browser to download the file

        return ($result);
    }
}
