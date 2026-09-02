<?php

/**
 * RoutingController.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\View\Components\Device\RoutingTabs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LibreNMS\Interfaces\UI\DeviceTab;

class RoutingController extends Controller implements DeviceTab
{
    public function __invoke(Device $device, Request $request): RedirectResponse
    {
        $this->authorize('view', $device);

        $routingTabs = RoutingTabs::getRoutingTabs($device);
        $tabKeys = array_keys($routingTabs);

        $proto = $request->query('proto') ?? $request->query('section') ?? $tabKeys[0] ?? 'cef';

        // Map any legacy names if needed (e.g. ipsec_tunnels -> ipsec-tunnels)
        $protoNormalized = str_replace('_', '-', $proto);

        $queryParams = $request->except(['proto', 'section']);

        if (\Route::has('device.routing.' . $protoNormalized)) {
            return redirect()->route('device.routing.' . $protoNormalized, array_merge(['device' => $device], $queryParams));
        }

        if (! empty($tabKeys) && \Route::has('device.routing.' . $tabKeys[0])) {
            return redirect()->route('device.routing.' . $tabKeys[0], array_merge(['device' => $device], $queryParams));
        }

        return redirect()->route('device.routing.cef', array_merge(['device' => $device], $queryParams));
    }

    public function visible(Device $device): bool
    {
        return ! empty(RoutingTabs::getRoutingTabs($device));
    }

    public function slug(): string
    {
        return 'routing';
    }

    public function icon(): string
    {
        return 'fa-random';
    }

    public function name(): string
    {
        return __('Routing');
    }

    public function data(Device $device, Request $request): array
    {
        return [];
    }
}
