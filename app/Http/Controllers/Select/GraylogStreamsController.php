<?php
/**
 * GraylogStreamsController.php
 *
 * Select for the available streams from the graylog server.
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Select;

use App\ApiClients\GraylogApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Log;

class GraylogStreamsController extends Controller
{
    /**
     * The default method called by the route handler
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request, GraylogApi $api)
    {
        $this->validate($request, [
            'limit' => 'int',
            'page' => 'int',
            'term' => 'nullable|string',
        ]);
        $search = strtolower($request->get('term'));

        $streams = [];
        try {
            $streams = collect($api->getStreams()['streams'])->filter(function ($stream) use ($search) {
                return ! $search || Str::contains(strtolower($stream['title']), $search) || Str::contains(strtolower($stream['description']), $search);
            })->map(function ($stream) {
                $text = $stream['title'];
                if ($stream['description']) {
                    $text .= " ({$stream['description']})";
                }

                return ['id' => $stream['id'], 'text' => $text];
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }

        return response()->json([
            'results' => $streams,
            'pagination' => ['more' => false],
        ]);
    }
}
