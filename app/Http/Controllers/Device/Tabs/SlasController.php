<?php

/**
 * SlasController.php
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
use App\Models\Device;
use App\Models\Sla;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LibreNMS\Interfaces\UI\DeviceTab;

class SlasController extends Controller implements DeviceTab
{
    /**
     * Extra graphs, by graph type => title, for SLA types that have more than the round trip time.
     */
    private const array TYPE_GRAPHS = [
        'icmpAppl' => ['device_sla_icmpAppl' => 'Packet Loss'],
        'IcmpEcho' => ['device_sla_IcmpEcho' => 'Packet Loss'],
        'IcmpTimeStamp' => ['device_sla_IcmpTimeStamp' => 'Packet Loss'],
        'icmpjitter' => [
            'device_sla_icmpjitter_latency' => 'Average Latency One Way',
            'device_sla_icmpjitter_jitter' => 'Average Jitter',
            'device_sla_icmpjitter_oos' => 'Packet Out of Sequence',
            'device_sla_icmpjitter_lost' => 'Lost Packets (Loss, Skipped, Late Arrival)',
            'device_sla_icmpjitter_iajitter' => 'Inter-Arrival Jitter (RFC 1889)',
        ],
        'jitter' => [
            'device_sla_jitter-latency' => 'Average Latency One Way',
            'device_sla_jitter' => 'Average Jitter',
            'device_sla_jitter-loss-percent' => 'Packet Loss (Percent)',
            'device_sla_jitter-loss' => 'Packet Loss (Count)',
            'device_sla_jitter-lost' => 'Lost Packets (Out Of Sequence, Tail Drop, Late Arrival)',
            'device_sla_jitter-mos' => 'Mean Opinion Score',
            'device_sla_jitter-icpif' => 'Impairment / Calculated Planning Impairment Factor',
        ],
    ];

    public function visible(Device $device): bool
    {
        return $device->slas()->exists();
    }

    public function slug(): string
    {
        return 'slas';
    }

    public function icon(): string
    {
        return 'fa-flag';
    }

    public function name(): string
    {
        return __('SLAs');
    }

    /**
     * @return array<string, mixed>
     */
    public function data(Device $device, Request $request): array
    {
        return [];
    }

    public function index(Device $device, Request $request): View|RedirectResponse
    {
        $this->authorize('view', $device);

        $request->validate([
            'id' => 'nullable|integer',
            'view' => 'nullable|string',
            'opstatus' => 'nullable|in:all,up,down',
        ]);

        // legacy /slas/id=X urls
        if ($request->filled('id')) {
            return redirect()->route('device.slas.show', ['device' => $device->device_id, 'sla' => $request->integer('id')], 301);
        }

        $slas = $device->slas()->where('deleted', 0)->orderBy('sla_nr')->get();
        $types = $slas->pluck('rtt_type')->unique()->mapWithKeys(fn ($type) => [$type => $this->typeName($type)])->sort()->all();
        $type = $request->string('view')->value() ?: 'all';
        $status = $request->string('opstatus')->value() ?: 'all';

        return view('device.tabs.slas', [
            'device' => $device,
            'slas' => $slas
                ->when($type !== 'all', fn ($slas) => $slas->where('rtt_type', $type))
                ->when($status === 'up', fn ($slas) => $slas->where('opstatus', 0))
                ->when($status === 'down', fn ($slas) => $slas->where('opstatus', '!=', 0)),
            'type' => $type,
            'types' => collect(['all' => __('All')] + $types)
                ->map(fn ($text, $option) => ['text' => $text, 'link' => $this->link($device, $option, $status)])->all(),
            'status' => $status,
            'statuses' => collect(['all' => __('All'), 'up' => __('Up'), 'down' => __('Down')])
                ->map(fn ($text, $option) => ['text' => $text, 'link' => $this->link($device, $type, $option)])->all(),
            'titles' => $slas->mapWithKeys(fn (Sla $sla) => [$sla->sla_id => $this->title($sla)]),
            'detailTypes' => array_keys(self::TYPE_GRAPHS),
        ]);
    }

    public function show(Device $device, Sla $sla): View
    {
        $this->authorize('view', $device);

        abort_if((bool) $sla->deleted, 404);

        return view('device.tabs.sla', [
            'device' => $device,
            'sla' => $sla,
            'title' => $this->title($sla),
            'graphs' => ['device_sla' => __('Round Trip Time')] + (self::TYPE_GRAPHS[$sla->rtt_type] ?? []),
        ]);
    }

    private function title(Sla $sla): string
    {
        return 'SLA #' . $sla->sla_nr . ' - ' . $this->typeName($sla->rtt_type)
            . ($sla->tag ? ': ' . $sla->tag : '')
            . ($sla->owner ? ' (' . __('Owner') . ': ' . $sla->owner . ')' : '');
    }

    private function typeName(string $type): string
    {
        $key = "modules.slas.types.$type";
        $translation = __($key);

        return $translation === $key ? ucfirst($type) : $translation;
    }

    private function link(Device $device, string $type, string $status): string
    {
        return route('device.slas', [
            'device' => $device->device_id,
            'view' => $type === 'all' ? null : $type,
            'opstatus' => $status === 'all' ? null : $status,
        ]);
    }
}
