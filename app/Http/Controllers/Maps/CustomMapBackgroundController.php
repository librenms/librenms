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
use App\Models\CustomMap;
use App\Models\CustomMapBackground;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;

class CustomMapBackgroundController extends Controller
{
    public function get(CustomMap $map)
    {
        $this->authorize('view', $map);

        $background = $this->checkImageCache($map);
        if ($background) {
            $path = Storage::disk('base')->path('html/images/custommap/background/' . $background);

            return response()->file($path, [
                'Content-Type' => Storage::mimeType($background),
            ]);
        }
        abort(404);
    }

    public function save(FormRequest $request, CustomMap $map)
    {
        $this->authorize('update', $map);
        $this->validate($request, [
            'type' => 'in:image,color,map,none',
            'image' => 'required_if:type,image|mimes:png,jpg,svg,gif',
            'color' => 'required_if:type,color|regex:/^#[0-9a-f]{6,8}$/',
            'lat' => 'required_if:type,map|numeric|between:-90,90',
            'lng' => 'required_if:type,map|numeric|between:-180,180',
            'zoom' => 'required_if:type,nap|integer|between:0,19',
        ]);

        $map->background_type = $request->type;
        $this->updateBackgroundImage($map, $request);
        $map->background_data = array_merge($map->background_data ?? [], $request->only([
            'color',
            'lat',
            'lng',
            'zoom',
        ]));

        $map->save();

        return response()->json([
            'bgtype' => $map->background_type,
            'bgdata' => $map->getBackgroundConfig(),
        ]);
    }

    private function clearImageCache(CustomMap $map): void
    {
        // if there are multiple web servers, it will only clear from the local.
        $imageName = $map->bgImageCacheFileName();
        if ($imageName && Storage::disk('base')->exists('html/images/custommap/background/' . $imageName)) {
            Storage::disk('base')->delete('html/images/custommap/background/' . $imageName);
        }
    }

    private function checkImageCache(CustomMap $map): ?string
    {
        if ($map->background_type !== 'image') {
            return null;
        }

        $imageName = $map->bgImageCacheFileName();
        if ($imageName && Storage::disk('base')->missing('html/images/custommap/background/' . $imageName)) {
            Storage::disk('base')->put('html/images/custommap/background/' . $imageName, $map->background->background_image);
        }

        return $imageName;
    }

    private function updateBackgroundImage(CustomMap $map, FormRequest $request): void
    {
        if ($map->background_type == 'image') {
            if ($request->image) {
                // if image type and we have image data (new image) save it
                $background = $map->background ?? new CustomMapBackground;
                $background->background_image = $request->image->getContent();
                $map->background()->save($background);
                $map->background_data = array_merge($map->background_data ?? [], [
                    'suffix' => $request->image->extension(),
                    'version' => md5($background->background_image),
                    'original_filename' => $request->image->getClientOriginalName(),
                ]);
            }
        } elseif ($map->getOriginal('background_type') == 'image') {
            // if no longer image, clean up
            $this->clearImageCache($map);
            $map->background()->delete();
            $map->background_data = array_diff_key($map->background_data ?? [], ['suffix' => 1, 'version' => 1, 'original_filename' => 1]);
        }
    }
}
