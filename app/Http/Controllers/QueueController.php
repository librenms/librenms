<?php

namespace App\Http\Controllers;

use App\Horizon\QueueStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QueueController extends Controller
{
    public function index(QueueStatus $status): View
    {
        $this->authorize('workers.horizon');
        abort_unless(QueueStatus::available(), 404);

        $retrying = $status->retrying();

        return view('poller.queues', [
            'current_tab' => 'queues',
            'queues' => $status->queues($retrying),
            'retrying' => $retrying,
            'workers' => $status->workers(),
            'failed' => $status->failedJobs(),
            'failed_count' => $status->failedCount(),
        ]);
    }

    public function retry(QueueStatus $status, string $id): RedirectResponse
    {
        $this->authorize('workers.horizon');
        abort_unless(QueueStatus::available(), 404);

        $status->retry($id);

        toast()->success(__('Job queued for retry'));

        return back();
    }

    public function forget(QueueStatus $status, string $id): RedirectResponse
    {
        $this->authorize('workers.horizon');
        abort_unless(QueueStatus::available(), 404);

        $status->forget($id);

        toast()->success(__('Failed job deleted'));

        return back();
    }
}
