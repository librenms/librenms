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
use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Syslog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LibreNMS\Util\Graylog;

class GraylogController extends Controller
{
    public function __invoke(Device $device, Request $request, GraylogApi $api): View
    {
        $this->authorize('view', $device);
        $this->authorize('viewAny', Syslog::class);  // Note: Graylog replaces syslog, correct permission?

        $request->validate([
            'stream' => 'nullable|string',
            'range' => 'nullable|int',
            'loglevel' => 'nullable|int|min:0|max:7',
        ]);

        return view('device.tabs.logs.graylog', Graylog::viewData($api, $device, $request));
    }
}
