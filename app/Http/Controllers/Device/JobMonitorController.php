<?php

namespace App\Http\Controllers\Device;

use App\Horizon\QueueStatus;
use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\RedirectResponse;

/**
 * Record the queue jobs of a device, shown in the capture tab
 */
class JobMonitorController extends Controller
{
    public function store(Device $device, QueueStatus $status): RedirectResponse
    {
        $this->authorize('workers.horizon');
        abort_unless(QueueStatus::available(), 404);

        $status->monitor(QueueStatus::deviceTag($device->device_id));

        return redirect()->route('device', ['device' => $device->device_id, 'tab' => 'capture', 'section' => 'jobs']);
    }

    public function destroy(Device $device, QueueStatus $status): RedirectResponse
    {
        $this->authorize('workers.horizon');
        abort_unless(QueueStatus::available(), 404);

        $status->stopMonitoring(QueueStatus::deviceTag($device->device_id));

        return redirect()->route('device', ['device' => $device->device_id, 'tab' => 'capture', 'section' => 'jobs']);
    }
}
