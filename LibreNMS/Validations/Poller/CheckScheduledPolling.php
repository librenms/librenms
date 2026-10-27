<?php

namespace LibreNMS\Validations\Poller;

use App\Facades\LibrenmsConfig;
use App\Horizon\QueueStatus;
use App\Jobs\DispatchPollingWork;
use App\Models\Device;
use Exception;
use LibreNMS\DB\Eloquent;
use LibreNMS\Interfaces\Validation;
use LibreNMS\ValidationResult;

/**
 * The scheduler dispatches polling work and devices are polled on time
 */
class CheckScheduledPolling implements Validation
{
    /** seconds without a completed dispatcher run before it is considered stopped */
    private const DISPATCH_LATE = 120;

    /**
     * @inheritDoc
     */
    public function validate(): ValidationResult
    {
        // without redis CheckHorizon fails, there is no Horizon data to check
        if (QueueStatus::available()) {
            try {
                $result = $this->checkDispatcher(app(QueueStatus::class));
            } catch (Exception $e) {
                return ValidationResult::fail(trans('validation.validations.poller.CheckHorizon.unavailable', ['message' => $e->getMessage()]));
            }

            if ($result !== null) {
                return $result;
            }
        }

        if (LibrenmsConfig::get('schedule_type.poller') == 'scheduler') {
            // last_polled is only updated when a device is up
            $overdue = Device::where('disabled', 0)
                ->where('status', 1)
                ->where('last_polled', '<', now()->subSeconds(DispatchPollingWork::pollInterval() * 2))
                ->pluck('hostname');

            if ($overdue->isNotEmpty()) {
                return ValidationResult::warn(trans('validation.validations.poller.CheckScheduledPolling.overdue'))
                    ->setList('Devices', $overdue->all());
            }
        }

        return ValidationResult::ok(trans('validation.validations.poller.CheckScheduledPolling.ok'));
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return Eloquent::isConnected() && CheckHorizon::schedulerPolling() && Device::exists();
    }

    private function checkDispatcher(QueueStatus $status): ?ValidationResult
    {
        $last = $status->lastDispatched();
        if ($last !== null && $last->diffInSeconds(now()) <= self::DISPATCH_LATE) {
            return null;
        }

        $result = $last === null
            ? ValidationResult::fail(trans('validation.validations.poller.CheckScheduledPolling.not_dispatched'))
            : ValidationResult::fail(trans('validation.validations.poller.CheckScheduledPolling.dispatch_stopped', ['time' => $last->diffForHumans()]));

        $failed = $status->failedJobs('dispatcher')->first();
        if ($failed !== null) {
            $result->setList('Last failure', [$failed['finished']?->diffForHumans() . ': ' . $failed['error']]);
        }

        return $result;
    }
}
