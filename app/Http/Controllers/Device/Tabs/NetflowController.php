<?php

/**
 * NetflowController.php
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

use App\Facades\LibrenmsConfig;
use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LibreNMS\Interfaces\UI\DeviceTab;
use LibreNMS\Util\Binaries;
use LibreNMS\Util\Nfsen;
use Symfony\Component\Process\Process;

class NetflowController extends Controller implements DeviceTab
{
    private const array STAT_TYPES = [
        'record' => 'Flow Records',
        'ip' => 'Any IP Address',
        'srcip' => 'SRC IP Address',
        'dstip' => 'DST IP Address',
        'port' => 'Any Port',
        'srcport' => 'SRC Port',
        'dstport' => 'DST Port',
        'srctos' => 'SRC TOS',
        'dsttos' => 'DST TOS',
        'tos' => 'TOS',
        'as' => 'AS',
        'srcas' => 'SRC AS',
        'dstas' => 'DST AS',
    ];

    private const array ORDERS = ['flows', 'packets', 'bytes', 'pps', 'bps', 'bpp'];

    public function visible(Device $device): bool
    {
        return LibrenmsConfig::get('nfsen_enable') && Nfsen::rrd($device->hostname) !== null;
    }

    public function slug(): string
    {
        return 'netflow';
    }

    public function icon(): string
    {
        return 'fa-tint';
    }

    public function name(): string
    {
        return __('Netflow');
    }

    /**
     * @return array<string, mixed>
     */
    public function data(Device $device, Request $request): array
    {
        return [];
    }

    public function index(Device $device, Request $request): View
    {
        $this->authorize('view', $device);

        $request->validate([
            'nfsen' => 'nullable|string',
            'channel' => 'nullable|string',
            'topN' => 'nullable|integer',
            'lastN' => 'nullable|integer',
            'stattype' => 'nullable|string',
            'statorder' => 'nullable|string',
        ]);

        $channels = Nfsen::channels($device->hostname);
        $channel = $request->string('channel')->value();
        $channel = isset($channels[$channel]) ? $channel : null;
        $view = $request->string('nfsen')->value();
        $view = match ($view) {
            'stats' => 'stats',
            'channel' => $channel ? 'channel' : 'general',
            default => 'general',
        };

        $data = [
            'view' => $view,
            'channel' => $channel,
            'options' => [
                'general' => ['text' => __('General'), 'link' => route('device.netflow', ['device' => $device->device_id, 'nfsen' => 'general'])],
                'stats' => ['text' => __('Stats'), 'link' => route('device.netflow', ['device' => $device->device_id, 'nfsen' => 'stats'])],
            ],
            'channels' => collect($channels)->map(fn ($rrd, $name) => [
                'text' => $name,
                'link' => route('device.netflow', ['device' => $device->device_id, 'nfsen' => 'channel', 'channel' => $name]),
            ])->all(),
        ];

        if ($view == 'channel') {
            $filter = dirname($channels[$channel]) . "/$channel-filter.txt";
            $data['filter'] = is_file($filter) ? file_get_contents($filter) : null;
        }

        if ($view == 'stats') {
            $data += $this->stats($device, $request);
        }

        return view('device.tabs.netflow', $data + ['device' => $device]);
    }

    /**
     * @return array<string, mixed>
     */
    private function stats(Device $device, Request $request): array
    {
        $topN = $request->integer('topN');
        $topN = $topN > 0 && $topN <= LibrenmsConfig::get('nfsen_top_max') ? $topN : (int) LibrenmsConfig::get('nfsen_top_default');
        $lastN = $request->integer('lastN');
        $lastN = $lastN > 0 && $lastN <= LibrenmsConfig::get('nfsen_last_max') ? $lastN : (int) LibrenmsConfig::get('nfsen_last_default');
        $type = $request->string('stattype')->value();
        $type = isset(self::STAT_TYPES[$type]) ? $type : LibrenmsConfig::get('nfsen_stats_default');
        $order = $request->string('statorder')->value();
        $order = in_array($order, self::ORDERS) ? $order : LibrenmsConfig::get('nfsen_order_default');

        return [
            'form' => [
                'action' => route('device.netflow', ['device' => $device->device_id] + []),
                'topN' => $topN,
                'lastN' => $lastN,
                'stattype' => $type,
                'statorder' => $order,
            ],
            'tops' => (array) LibrenmsConfig::get('nfsen_top_N'),
            'lasts' => (array) LibrenmsConfig::get('nfsen_lasts'),
            'types' => self::STAT_TYPES,
            'orders' => self::ORDERS,
            'output' => $request->has('process') ? $this->nfdump($device, $topN, $lastN, "$type/$order") : null,
        ];
    }

    private function nfdump(Device $device, int $topN, int $lastN, string $stat): string
    {
        $dir = Nfsen::liveDir($device->hostname);
        if ($dir === null) {
            return __('NfSen base directory not found, check nfsen_base');
        }

        $end = time() - 300;
        $start = $end - $lastN - 300;

        $process = new Process([
            resolve(Binaries::class)->path('nfdump'),
            '-M', $dir,
            '-T',
            '-R', Nfsen::subPath($start) . ':' . Nfsen::subPath($end),
            '-n', (string) $topN,
            '-s', $stat,
        ]);
        $process->run();

        return $process->getOutput() . $process->getErrorOutput();
    }
}
