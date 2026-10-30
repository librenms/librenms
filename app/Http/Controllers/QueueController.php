<?php

namespace App\Http\Controllers;

use App\Queue\FailedJobs;
use App\Queue\QueueStatus;
use App\Queue\RetryTracker;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QueueController extends Controller
{
    public function index(QueueStatus $status, RetryTracker $retries, FailedJobs $failed): View
    {
        $this->authorize('worker.view');
        abort_unless(QueueStatus::showPages(), 404);

        return view('poller.queues', [
            'current_tab' => 'queues',
            'queues' => $status->queues(),
            'retrying' => $retries->retrying(),
            'workers' => $status->workers(),
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

    public function forget(FailedJobs $failed, string $id): RedirectResponse
    {
        $this->authorize('worker.update');
        abort_unless(QueueStatus::showPages(), 404);

        $failed->forget($id);

        toast()->success(__('Failed job deleted'));

        return back();
    }
}
