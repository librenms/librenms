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

use App\Models\User;
use App\Plugins\Hooks\ResourceAccessorHook;
use Illuminate\Http\Request;

// This ResourceHook is called from the PluginResourceController, and allows the plugin to publish files, images, etc

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
    //
    //   -> May throw \Symfony\Component\HttpKernel\Exception\HttpException to return HTTP error codes (https://laravel.com/docs/errors#http-exceptions)

    public function processRequest(Request $request, string $path, array $settings)
    {
        // you are free to change the path here. You can also generate files on the fly.
        $full_path = base_path('/app/Plugins/ExamplePlugin/files/' . $path);

        // if you use a real file, better check if it exists and is readable, and return the proper abort instruction for the framework
        if (! is_file($full_path)) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(
                statusCode: 404,
            );
        }
        if (! is_readable($full_path)) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(
                statusCode: 403,
            );
        }

        // Various possible responses() methods are documented here : https://laravel.com/docs/responses

        // to return an image or a file to be displayed inline
        $result = response()->file($full_path);

        // tell the browser to download the file in $full_path and save it as $name
        //$result = response()->download($full_path, $name);

        // to build dynamically a file from code and expose it as $name for the browser
        //$result = response()->streamDownload(function() {  
        //    echo "Content of the file";
        //}, $name);

        return $result;
    }
}
