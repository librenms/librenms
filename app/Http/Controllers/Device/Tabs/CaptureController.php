<?php

/**
 * CaptureController.php
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

use App\Horizon\JobMonitor;
use App\Models\Device;
use App\Queue\QueueStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Interfaces\UI\DeviceTab;

class CaptureController implements DeviceTab
{
    public function visible(Device $device): bool
    {
        return false;
    }

    public function slug(): string
    {
        return 'capture';
    }

    public function icon(): string
    {
        return 'fa-bug';
    }

    public function name(): string
    {
        return __('Capture');
    }

    public function data(Device $device, Request $request): array
    {
        Gate::authorize('device.debug');

        return [
            'section' => $request->query('section', 'discovery'),
            'jobs' => $this->jobs($device),
            'tabs' => [
                'discovery' => [
                    'name' => __('Discovery'),
                    'url' => route('device.debug.process', ['device' => $device, 'format' => 'text', 'type' => 'discovery', 'verbose' => 1]),
                ],
                'poller' => [
                    'name' => __('Poller'),
                    'url' => route('device.debug.process', ['device' => $device, 'format' => 'text', 'type' => 'poller', 'verbose' => 1]),
                ],
                'snmp' => [
                    'name' => __('SNMP'),
                    'url' => route('device.debug.snmp', ['device' => $device, 'format' => 'text']),
                ],
                'alerts' => [
                    'name' => __('Alerts'),
                    'url' => route('device.debug.alerts', ['device' => $device, 'format' => 'text']),
                ],
            ],
        ];
    }

    /**
     * Queue jobs for the device, null when queue workers are not in use.
     * Every job is only recorded when Horizon runs the workers.
     *
     * @return array{monitor: bool, monitoring: bool, jobs: Collection<int, array<string, mixed>>, failed: Collection<int, array<string, mixed>>}|null
     */
    private function jobs(Device $device): ?array
    {
        if (! QueueStatus::showPages() || Gate::denies('worker.view')) {
            return null;
        }

        $monitor = app(JobMonitor::class);
        $available = $monitor->available();
        $tag = JobMonitor::deviceTag($device->device_id);
        $monitoring = $available && $monitor->isMonitoring($tag);

        return [
            'monitor' => $available,
            'monitoring' => $monitoring,
            'jobs' => $monitoring ? $monitor->monitoredJobs($tag) : collect(),
            'failed' => app(QueueStatus::class)->failedJobs(device_id: $device->device_id),
        ];
    }
}
