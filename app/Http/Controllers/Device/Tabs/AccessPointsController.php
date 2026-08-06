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

use App\Models\Device;
use Illuminate\Http\Request;
use LibreNMS\Interfaces\UI\DeviceTab;
use LibreNMS\Util\Url;

class AccessPointsController implements DeviceTab
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
        $accessPointId = (int) Url::parseOptions('ap', 0);
        $accessPoint = $accessPointId > 0
            ? $device->accessPoints()->where('deleted', false)->find($accessPointId)
            : null;

        return [
            'accessPoint' => $accessPoint,
            'graphs' => [
                ['type' => 'accesspoints_numasoclients', 'title' => __('Associated Clients')],
                ['type' => 'accesspoints_interference', 'title' => __('Interference')],
                ['type' => 'accesspoints_channel', 'title' => __('Channel')],
                ['type' => 'accesspoints_txpow', 'title' => __('Transmit Power')],
                ['type' => 'accesspoints_radioutil', 'title' => __('Radio Utilization')],
                ['type' => 'accesspoints_nummonclients', 'title' => __('Monitored Clients')],
                ['type' => 'accesspoints_nummonbssid', 'title' => __('Number of monitored BSSIDs')],
            ],
        ];
    }
}
