<?php

namespace App\Http\Controllers;

use App\Jobs\PollDevice;
use App\Models\Device;
use App\Queue\DeviceLocks;
use App\Queue\FailedJobs;
use App\Queue\QueueStatus;
use App\Queue\RetryTracker;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use LibreNMS\Util\ModuleList;

class QueueController extends Controller
{
    public function index(QueueStatus $status, RetryTracker $retries, FailedJobs $failed, DeviceLocks $locks): View
    {
        $this->authorize('worker.view');
        abort_unless(QueueStatus::showPages(), 404);

        $retrying = $retries->retrying();
        $overdue = $status->overdueDevices(50);

        return view('poller.queues', [
            'current_tab' => 'queues',
            'queues' => $status->queues($retrying),
            'retrying' => $retrying,
            'workers' => $status->workers(),
            'overdue' => $overdue->map(fn (Device $device): array => ['device' => $device, 'locked' => $locks->held($device)]),
            'lock_minutes' => intdiv((new PollDevice(0, ModuleList::fromUserOverrides([])))->uniqueFor(), 60),
            'failed' => $failed->list(),
            'failed_count' => $failed->count(),
        ]);
    }

    public function retry(FailedJobs $failed, string $id): RedirectResponse
    {
        $this->authorize('worker.update');
        abort_unless(QueueStatus::showPages(), 404);

        $failed->retry($id);

        toast()->success(__('Job queued for retry'));

        return back();
    }

    public function releaseLock(DeviceLocks $locks, Device $device): RedirectResponse
    {
        $this->authorize('worker.update');
        abort_unless(QueueStatus::showPages(), 404);

        $locks->release($device);

        toast()->success(__('The device will be queued on the next dispatch'));

        return back();
    }

    public function forget(FailedJobs $failed, string $id): RedirectResponse
    {
        $this->authorize('worker.update');
        abort_unless(QueueStatus::showPages(), 404);

        $failed->forget($id);

        toast()->success(__('Failed job deleted'));

        return back();
    }
}
