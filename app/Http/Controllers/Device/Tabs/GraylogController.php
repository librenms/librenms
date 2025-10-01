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

use App\Facades\LibrenmsConfig;
use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GraylogController extends Controller
{
    public function __invoke(Device $device, Request $request): View
    {
        $request->validate([
            'stream' => 'nullable|string',
            'range' => 'nullable|int',
            'loglevel' => 'nullable|int',
        ]);

        return view('device.tabs.logs.graylog', [
            'device' => $device,
            'timezone' => LibrenmsConfig::has('graylog.timezone'),
            'filter_device' => true,
            'show_form' => true,
            'stream' => $request->input('stream', ''),
            'range' => $request->input('range', '0'),
            'loglevel' => $request->input('loglevel', ''),
        ]);
    }
}
