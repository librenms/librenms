<?php

/**
 * TopErrorsController.php
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
 * @copyright  2018 Tony Murray
 * @copyright  2021 PipoCanaja
 * @author     Tony Murray <murraytony@gmail.com>
 * @author     PipoCanaja
 */

namespace App\Http\Controllers\Widgets;

use App\Models\Port;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TopErrorsController extends WidgetController
{
    protected string $name = 'top-errors';
    protected $defaults = [
        'interface_count' => 5,
        'time_interval' => 15,
        'interface_filter' => null,
        'device_group' => null,
        'port_group' => null,
    ];

    public function getView(Request $request): string|View
    {
        $data = $this->getSettings();

        $query = Port::hasAccess($request->user())->with(['device' => function ($query): void {
            $query->select('device_id', 'hostname', 'sysName', 'display', 'status', 'os');
        }])
            ->isValid()
            ->select(['port_id', 'device_id', 'ifName', 'ifDescr', 'ifAlias'])
            ->groupBy('port_id', 'device_id', 'ifName', 'ifDescr', 'ifAlias')
            ->where('poll_time', '>', Carbon::now()->subMinutes($data['time_interval'])->timestamp)
            ->where(fn ($query) => $query
                ->where('ifInErrors_rate', '>', 0)
                ->orWhere('ifOutErrors_rate', '>', 0))
            ->isUp()
            ->when($data['device_group'], fn ($query) => $query->inDeviceGroup($data['device_group']), fn ($query) => $query->has('device'))
            ->when($data['port_group'], fn ($query) => $query->inPortGroup($data['port_group']))
            ->orderByRaw('SUM(LEAST(ifInErrors_rate, 9223372036854775807) + LEAST(ifOutErrors_rate, 9223372036854775807)) DESC')
            ->limit($data['interface_count']);

        if ($data['interface_filter']) {
            $query->where('ifType', '=', $data['interface_filter']);
        }

        $data['ports'] = $query->get();

        return view('widgets.top-errors', $data);
    }
}
