<?php
/**
 * CustomMapController.php
 *
 * Controller for custom maps
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
 * @copyright  2023 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.com.au>
 */

namespace App\Http\Controllers\Maps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LibreNMS\Config;
use LibreNMS\Util\Url;

class CustomMapController extends Controller
{
    public function edit(Request $request)
    {
        // TODO: Check if admin

        // TODO: Read config if ID > 0
        $map_conf = [
            'height' => "800px",
            'width' => "1800px",
            'interaction' => [
                'dragNodes' => true,
                'dragView' => false,
                'zoomView' => false,
            ],
            'manipulation' => [
                'enabled' => true,
                'initiallyActive' => true,
            ],
            'physics' => [
                'enabled' => false,
            ],
        ];

        // TODO: Allow settings to come from DB
        $newedge_conf = [
            'arrows' => [
                'to' => [
                    'enabled' => true,
                ],
            ],
            'smooth' => [
                'type' => "dynamic",
            ],
            'font' => [
                'color' => '#343434',
                'size' => 14,
                'face' => 'arial',
            ],
            'label' => true,
        ];

        $newnode_conf = [
            'borderWidth' => 1,
            'color' => [
                'border' => '#2B7CE9',
                'background' => '#D2E5FF',
            ],
            'font' => [
                'color' => '#343434',
                'size' => 14,
                'face' => 'arial',
            ],
            'icon' => [],
            'label' => true,
            'shape' => 'box',
            'size' => 25,
        ];

        $data = [
            'edit' => true,
            'map_id' => $request->map_id,
            'name' => 'New Map',
            'background' => null,
            'page_refresh' => Config::get('page_refresh', 300),
            'map_conf' => $map_conf,
            'newedge_conf' => $newedge_conf,
            'newnode_conf' => $newnode_conf,
            'vmargin' => 20,
            'hmargin' => 20,
        ];

        return view('map.custom', $data);
    }

    public function save(Request $request)
    {
        $errors = [];

        $map_id = Url::parseOptions('map_id');
        $name = $request->post('name');
        $width = $request->post('width');
        $height = $request->post('height');
        $bgclear = $request->post('bgclear');
        $bgnewimage = $request->post('bgimage');

        if (! preg_match('/^(\d+)(px|%)$/', $width, $matches)) {
            array_push($errors, "Width must be a number followed by px or %");
        } elseif ($matches[2] == 'px' && $matches[1] < 200) {
            array_push($errors, "Width in pixels must be at least 200");
        } elseif ($matches[2] == '%' && ($matches[1] < 10 || $matches[1] > 100)) {
            array_push($errors, "Width percent must be between 10 and 100");
        }

        if (! preg_match('/^(\d+)(px|%)$/', $height, $matches)) {
            array_push($errors, "Height must be a number followed by px or %");
        } elseif ($matches[2] == 'px' && $matches[1] < 200) {
            array_push($errors, "Height in pixels must be at least 200");
        } elseif ($matches[2] == '%' && ($matches[1] < 10 || $matches[1] > 100)) {
            array_push($errors, "Height percent must be between 10 and 100");
        }

        if (! $name) {
            array_push($errors, "Name must be supplied");
        }

        if ($bgnewimage) {
            $request->validate(['bgimage' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048']);
        }

        $imageName = null;
        if (! $errors) {
            //TODO: Update or create
            //TODO: Make sure background is left alone if bgclear is false and bgnewimage is null
            if (! $map_id) {
                //TODO: Replace with new map ID
                $map_id = 1;
            }

            if ($request->bgimage) {
                $imageName = $map_id . '.' . $request->bgimage->extension();
                $request->bgimage->move(public_path('images/custommap'), $imageName);
                //TODO: Update database again with image name and image contents
            }
            //TODO: Set imageName if there is an existing image in the DB
        }

        return response()->json(['id' => $map_id, 'bgimage' => $imageName, 'errors' => $errors]);
    }
}
