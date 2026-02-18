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
            'device' => 'nullable|int',
            'from' => 'nullable|string',
            'to' => 'nullable|string',
            'level' => 'nullable|string',
        ]);
        $syslog_program_filter = [
            'field' => 'program',
            'device' => $device->device_id,
        ];
        $syslog_priority_filter = [
            'field' => 'priority',
            'device' => $device->device_id,
        ];

        $format = LibrenmsConfig::get('dateformat.byminute', 'Y-m-d H:i');
        $now = Carbon::now();
        $defaultFrom = (clone $now)->subDays(7);
        $fromInput = $request->input('from');
        $toInput = $request->input('to');

        if (empty($fromInput) && empty($toInput)) {
            $fromInput = $defaultFrom->format($format);
            $toInput = $now->format($format);
        }

        return view('device.tabs.logs.syslog', [
            'device' => $device,
            'now' => $now->format($format),
            'default_date' => $defaultFrom->format($format),
            'program' => $request->input('program', ''),
            'priority' => $request->input('priority', ''),
            'from' => $fromInput,
            'to' => $toInput,
            'syslog_program_filter' => $syslog_program_filter,
            'syslog_priority_filter' => $syslog_priority_filter,
        ]);
    }
}
