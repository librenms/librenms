<?php

/**
 * VmInfoController.php
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

use App\Facades\Rrd;
use App\Models\Device;
use App\Models\Vminfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use LibreNMS\Interfaces\UI\DeviceTab;

class VmInfoController implements DeviceTab
{
    public function visible(Device $device): bool
    {
        return Gate::allows('viewAny', Vminfo::class) && $device->vminfo()->exists();
    }

    public function slug(): string
    {
        return 'vm';
    }

    public function icon(): string
    {
        return 'fa-cog';
    }

    public function name(): string
    {
        return __('Virtual Machines');
    }

    public function data(Device $device, Request $request): array
    {
        $request->validate(Vminfo::filterValidationRules());

        return [
            'filterFields' => collect(Vminfo::filterFieldDefinitions())
                ->filter(fn ($field) => $field['key'] !== 'device_id')->values()->all(),
            'filter' => $request->array('filter'),
        ];
    }

    public function show(Device $device, Vminfo $vminfo): View
    {
        abort_unless($vminfo->device()->is($device), 404);
        Gate::authorize('view', $vminfo);

        $graphs = [
            ['type' => 'vminfo_cpu', 'metric' => 'vm.cpu', 'title' => __('CPU Usage')],
            ['type' => 'vminfo_mem', 'metric' => 'vm.memory', 'title' => __('Memory Usage')],
            ['type' => 'vminfo_disk', 'metric' => 'vm.disk', 'title' => __('Disk Usage')],
            ['type' => 'vminfo_diskio_bits', 'metric' => 'vm.diskio', 'title' => __('Disk I/O')],
            ['type' => 'vminfo_diskio_ops', 'metric' => 'vm.diskio', 'title' => __('Disk Operations')],
            ['type' => 'vminfo_net', 'metric' => 'vm.net', 'title' => __('Network Traffic')],
        ];

        return view('device.tabs.vm.show', [
            'device' => $device,
            'vm' => $vminfo,
            // not every source records metrics
            'graphs' => array_values(array_filter($graphs, fn (array $graph): bool => Rrd::checkRrdExists(
                Rrd::name($device->hostname, [$graph['metric'], ...$vminfo->tags()])
            ))),
        ]);
    }
}
