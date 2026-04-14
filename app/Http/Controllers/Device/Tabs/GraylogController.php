<?php

/**
 * GraylogController.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs;

use App\ApiClients\GraylogApi;
use App\Facades\LibrenmsConfig;
use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GraylogController extends Controller
{
    public function __invoke(Device $device, Request $request, GraylogApi $api): View
    {
        $request->validate([
            'stream' => 'nullable|string',
            'range' => 'nullable|int',
            'loglevel' => 'nullable|int',
        ]);

        $stream = $request->input('stream') ?: (string) LibrenmsConfig::get('graylog.device-page.default-stream-id', '');
        $stream_selected = $stream !== '' ? ['id' => $stream, 'text' => $this->resolveStreamText($api, $stream)] : null;

        return view('device.tabs.logs.graylog', [
            'device' => $device,
            'timezone' => LibrenmsConfig::has('graylog.timezone'),
            'filter_device' => true,
            'show_form' => true,
            'stream' => $stream,
            'stream_selected' => $stream_selected,
            'range' => $request->input('range', '28800'),
            'loglevel' => $request->input('loglevel', ''),
            'fields' => (array) LibrenmsConfig::get('graylog.device-page.fields', ['severity', 'origin', 'level', 'source', 'message', 'facility']),
        ]);
    }

    private function resolveStreamText(GraylogApi $api, string $streamId): string
    {
        try {
            foreach ($api->getStreams()['streams'] ?? [] as $stream) {
                if (($stream['id'] ?? null) === $streamId) {
                    $text = $stream['title'] ?? $streamId;
                    if (! empty($stream['description'])) {
                        $text .= " ({$stream['description']})";
                    }

                    return $text;
                }
            }
        } catch (\Exception $e) {
            // Fall through to the raw id on API failure
        }

        return $streamId;
    }
}
