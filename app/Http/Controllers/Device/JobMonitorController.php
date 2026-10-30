<?php

namespace App\Http\Controllers\Device;

use App\Horizon\JobMonitor;
use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Queue\QueueStatus;
use Illuminate\Http\RedirectResponse;

/**
 * Record the queue jobs of a device with Horizon, shown in the capture tab
 */
class JobMonitorController extends Controller
{
    public function store(Device $device, JobMonitor $monitor): RedirectResponse
    {
        $this->authorize('worker.update');
        $this->authorize('view', $device);
        abort_unless(QueueStatus::showPages() && $monitor->available(), 404);

        $monitor->monitor(JobMonitor::deviceTag($device->device_id));

        return redirect()->route('device', ['device' => $device->device_id, 'tab' => 'capture', 'section' => 'jobs']);
    }

    public function destroy(Device $device, JobMonitor $monitor): RedirectResponse
    {
        $this->authorize('worker.update');
        $this->authorize('view', $device);
        abort_unless(QueueStatus::showPages() && $monitor->available(), 404);

        $monitor->stopMonitoring(JobMonitor::deviceTag($device->device_id));

        return redirect()->route('device', ['device' => $device->device_id, 'tab' => 'capture', 'section' => 'jobs']);
    }
}
