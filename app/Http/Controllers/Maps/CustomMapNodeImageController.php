<?php
/**
 * CustomMapNodeImageController.php
 *
 * Controller for custom map node images
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
 * @copyright  2024 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.com.au>
 */

namespace App\Http\Controllers\Maps;

use App\Http\Controllers\Controller;
use App\Models\CustomMapNodeImage;
use enshrined\svgSanitize\Sanitizer;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class CustomMapNodeImageController extends Controller
{
    public function index(): View
    {
        $this->authorize('update');

        return view('map.custom-nodeimage-manage', [
            'images' => CustomMapNodeImage::orderBy('name')->get(),
        ]);
    }

    public function show(CustomMapNodeImage $image)
    {
        // explicitly use file cache
        try {
            $imageContent = Cache::driver('file')
                ->remember($this->getCacheKey($image), new \DateInterval('P30D'), fn () => $image->image);
        } catch (\ErrorException $e) {
            // if cache fails, just load from database :(
            $imageContent = $image->image;
        }

        if (empty($imageContent)) {
            abort(404);
        }

        return response($imageContent, headers: [
            'Content-Type' => $image->mime ?? getimagesizefromstring($imageContent)['mime'] ?? 'image/jpeg',
        ]);
    }

    public function store(FormRequest $request): JsonResponse
    {
        $this->authorize('update');
        $this->validate($request, [
            'image' => 'image|mimes:png,jpg,svg,gif',
            'name' => 'string',
        ]);

        if (! $request->image) {
            return response()->json([
                'message' => 'No image was supplied',
            ]);
        }

        $image = new CustomMapNodeImage;
        $this->updateImage($request, $image);
        $image->save();

        return response()->json([
            'result' => 'success',
            'id' => $image->custom_map_node_image_id,
            'name' => $image->name,
            'version' => $image->version,
        ]);
    }

    public function update(FormRequest $request, CustomMapNodeImage $image): JsonResponse
    {
        $this->authorize('update', $image);
        $this->validate($request, [
            'image' => 'image|mimes:png,jpg,svg,gif',
            'name' => 'string',
        ]);

        $this->updateImage($request, $image);
        $image->save();

        return response()->json([
            'result' => 'success',
            'name' => $request['name'],
            'version' => $image->version,
        ]);
    }

    public function destroy(CustomMapNodeImage $image): Response
    {
        $this->authorize('update', $image);
        if ($image->nodes->count() > 0) {
            return response('Image is in use', 403)
                      ->header('Content-Type', 'text/plain');
        }

        $image->delete();

        return response('Success', 200)
                  ->header('Content-Type', 'text/plain');
    }

    private function updateImage(FormRequest $request, CustomMapNodeImage $image)
    {
        if ($request->has('image')) {
            $image_content = $request->image->getContent();
            $mimeType = $request->image->getMimeType();

            // sanitize SVGs
            if ($mimeType == 'image/svg+xml') {
                $image_content = (new Sanitizer)->sanitize($image_content);
            }

            Cache::driver('file')->forget($this->getCacheKey($image)); // clear old image cache if present

            $image->image = $image_content;
            $image->version = md5($image_content);
            $image->mime = $mimeType;
        }
        $image->name = $request->name;
    }

    private function getCacheKey(CustomMapNodeImage $image): string
    {
        return 'custommap_nodeimage_' . $image->custom_map_node_image_id . ':' . ($image->version ?? '');
    }
}
