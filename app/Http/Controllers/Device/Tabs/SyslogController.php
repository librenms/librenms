<?php
/**
 * SyslogController.php
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
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class SyslogController extends Controller
{
    public function __invoke(Device $device, Request $request): View
    {
        $request->validate([
            'program' => 'nullable|string',
            'priority' => 'nullable|string',
            'from' => 'nullable|string',
            'to' => 'nullable|string',
        ]);

        return view('device.tabs.logs.syslog', [
            'device' => $device,
            'filter_device' => false,
            'now' => Carbon::now()->format(LibrenmsConfig::get('dateformat.byminute', 'Y-m-d H:i')),
            'default_date' => Carbon::now()->subDay()->format(LibrenmsConfig::get('dateformat.byminute', 'Y-m-d H:i')),
            'program' => $request->input('program', ''),
            'priority' => $request->input('priority', ''),
            'from' => $request->input('from', ''),
            'to' => $request->input('to', ''),
        ]);
    }
}
