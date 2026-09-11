<?php

/**
 * AccessPointsController.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs;

use App\Http\Controllers\Controller;
use App\Models\AccessPoint;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LibreNMS\Interfaces\UI\DeviceTab;

class AccessPointsController extends Controller implements DeviceTab
{
    public function visible(Device $device): bool
    {
        return $device->accessPoints()->exists();
    }

    public function slug(): string
    {
        return 'accesspoints';
    }

    public function icon(): string
    {
        return 'fa-wifi';
    }

    public function name(): string
    {
        return __('Access Points');
    }

    public function data(Device $device, Request $request): array
    {
        return [];
    }

    public function show(Device $device, AccessPoint $accessPoint): View
    {
        $this->authorize('view', $device);
        abort_if($accessPoint->deleted, 404);

        return view('device.tabs.accesspoints.show', [
            'device' => $device,
            'accessPoint' => $accessPoint,
            'graphs' => [
                ['type' => 'accesspoints_numasoclients', 'title' => __('Associated Clients')],
                ['type' => 'accesspoints_interference', 'title' => __('Interference Index')],
                ['type' => 'accesspoints_channel', 'title' => __('Channel')],
                ['type' => 'accesspoints_txpow', 'title' => __('Transmit Power')],
                ['type' => 'accesspoints_radioutil', 'title' => __('Radio Utilization')],
                ['type' => 'accesspoints_nummonclients', 'title' => __('Monitored Clients')],
                ['type' => 'accesspoints_nummonbssid', 'title' => __('Number of monitored BSSIDs')],
            ],
        ]);
    }
}
