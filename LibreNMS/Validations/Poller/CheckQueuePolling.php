<?php

namespace LibreNMS\Validations\Poller;

use App\Jobs\DispatchPollingWork;
use App\Models\Device;
use App\Queue\FailedJobs;
use App\Queue\QueueStatus;
use App\Queue\WorkerNode;
use Exception;
use LibreNMS\DB\Eloquent;
use LibreNMS\Interfaces\Validation;
use LibreNMS\ValidationResult;

/**
 * The scheduler dispatches polling work and devices are polled on time
 */
class CheckQueuePolling implements Validation
{
    /** seconds without a completed DispatchPollingWork run before it is considered stopped */
    private const DISPATCH_LATE = 120;

    /**
     * @inheritDoc
     */
    public function validate(): ValidationResult
    {
        try {
            $result = $this->checkDispatcher(app(QueueStatus::class), app(FailedJobs::class));
        } catch (Exception $e) {
            return ValidationResult::fail(trans('validation.validations.poller.CheckQueueWorkers.unavailable', ['message' => $e->getMessage()]));
        }

        if ($result !== null) {
            return $result;
        }

        if (WorkerNode::inUse('poller')) {
            $overdue = app(QueueStatus::class)->overdueDevices()->pluck('hostname');

            if ($overdue->isNotEmpty()) {
                return ValidationResult::warn(trans('validation.validations.poller.CheckQueuePolling.overdue'), trans('validation.validations.poller.CheckQueuePolling.overdue_fix'))
                    ->setList('Devices', $overdue->all());
            }
        }

        return ValidationResult::ok(trans('validation.validations.poller.CheckQueuePolling.ok'));
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return Eloquent::isConnected() && WorkerNode::inUse() && Device::exists();
    }

    private function checkDispatcher(QueueStatus $status, FailedJobs $failedJobs): ?ValidationResult
    {
        $last = $status->lastDispatched();
        if ($last !== null && $last->diffInSeconds(now()) <= self::DISPATCH_LATE) {
            return null;
        }

        $result = $last === null
            ? ValidationResult::fail(trans('validation.validations.poller.CheckQueuePolling.not_dispatched'))
            : ValidationResult::fail(trans('validation.validations.poller.CheckQueuePolling.dispatch_stopped', ['time' => $last->diffForHumans()]));

        $failed = $failedJobs->list(job: DispatchPollingWork::class, limit: 1)->first();
        if ($failed !== null) {
            $result->setList('Last failure', [$failed['finished']?->diffForHumans() . ': ' . $failed['error']]);
        }

        return $result;
    }
}
