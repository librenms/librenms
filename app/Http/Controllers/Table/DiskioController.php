<?php

/**
 * DiskioController.php
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

namespace App\Http\Controllers\Table;

use App\Models\UcdDiskio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use LibreNMS\Util\Url;

class DiskioController extends TableController
{
    protected function sortFields($request): array
    {
        return [
            'device_hostname',
            'diskio_descr',
        ];
    }

    protected function searchFields(Request $request): array
    {
        return [
            'hostname',
            'diskio_descr',
        ];
    }

    protected function baseQuery(Request $request)
    {
        return UcdDiskio::query()
            ->hasAccess($request->user())
            ->when($request->get('searchPhrase'), fn ($q) => $q->leftJoin('devices', 'devices.device_id', '=', 'sensors.device_id'))
            ->withAggregate('device', 'hostname');
    }

    /**
     * @param  UcdDiskio  $diskio
     */
    public function formatItem($diskio): array
    {
        $graph_array = [
            'type' => 'diskio_bits',
            'popup_title' => htmlentities(strip_tags($diskio->device?->displayName() . ': ' . $diskio->diskio_descr)),
            'id' => $diskio->diskio_id,
            'from' => '-1d',
            'height' => 20,
            'width' => 80,
        ];

        $hostname = Blade::render('<x-device-link :device="$device" />', ['device' => $diskio->device]);
        $bits_graph = Url::graphPopup($graph_array);
        $graph_array['type'] = 'diskio_ops';
        $ops_graph = Url::graphPopup($graph_array);

        return [
            'device_hostname' => $hostname,
            'diskio_descr' => $diskio->diskio_descr,
            'bits_graph' => $bits_graph,
            'ops_graph' => $ops_graph,
        ];
    }
}
