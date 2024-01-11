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
use Illuminate\Support\Facades\Storage;

class CustomMapBackgroundController extends Controller
{
    public function __invoke(CustomMap $map)
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

    private function checkImageCache(CustomMap $map): ?string
    {
        if (! $map->background_suffix) {
            return null;
        }

        $imageName = $map->custom_map_id . '_' . $map->background_version . '.' . $map->background_suffix;
        if (Storage::disk('base')->missing('html/images/custommap/background/' . $imageName)) {
            Storage::disk('base')->put('html/images/custommap/background/' . $imageName, $map->background->background_image);
        }

        return $imageName;
    }

}
