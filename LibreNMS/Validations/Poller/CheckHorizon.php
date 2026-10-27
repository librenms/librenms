<?php

namespace LibreNMS\Validations\Poller;

use App\Facades\LibrenmsConfig;
use App\Horizon\QueueStatus;
use App\Jobs\DiscoverDevice;
use App\Jobs\DispatchPollingWork;
use App\Jobs\PollDevice;
use App\Models\Device;
use Exception;
use LibreNMS\DB\Eloquent;
use LibreNMS\Interfaces\Validation;
use LibreNMS\Util\Binaries;
use LibreNMS\ValidationResult;

/**
 * Horizon is running and works every queue the dispatcher uses
 */
class CheckHorizon implements Validation
{
    /**
     * @inheritDoc
     */
    public function validate(): ValidationResult
    {
        if (! QueueStatus::available()) {
            return ValidationResult::fail(trans('validation.validations.poller.CheckHorizon.queue_driver', ['driver' => config('queue.default')]), 'Set QUEUE_CONNECTION=redis in .env');
        }

        try {
            $status = app(QueueStatus::class);

            if ($status->workers()->isEmpty()) {
                return ValidationResult::fail(trans('validation.validations.poller.CheckHorizon.not_running'))
                    ->setFix($this->serviceCommands());
            }

            $intervals = self::queueIntervals();
            $unworked = array_diff(array_keys($intervals), $status->workedQueues());
            if (! empty($unworked)) {
                return ValidationResult::fail(trans('validation.validations.poller.CheckHorizon.unworked'))
                    ->setList('Queues', array_values($unworked));
            }

            // wait is Horizon's estimate of the time to clear the jobs that are due
            $behind = $status->queues(collect())
                ->filter(fn (array $queue): bool => isset($intervals[$queue['name']]) && $queue['wait'] > $intervals[$queue['name']])
                ->map(fn (array $queue): string => "{$queue['name']} ({$queue['wait']}s)");
            if ($behind->isNotEmpty()) {
                return ValidationResult::warn(trans('validation.validations.poller.CheckHorizon.behind'))
                    ->setList('Queues', $behind->values()->all());
            }
        } catch (Exception $e) {
            return ValidationResult::fail(trans('validation.validations.poller.CheckHorizon.unavailable', ['message' => $e->getMessage()]));
        }

        return ValidationResult::ok(trans('validation.validations.poller.CheckHorizon.ok'));
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return Eloquent::isConnected() && self::schedulerPolling();
    }

    public static function schedulerPolling(): bool
    {
        return LibrenmsConfig::get('schedule_type.poller') == 'scheduler' || LibrenmsConfig::get('schedule_type.discovery') == 'scheduler';
    }

    /**
     * Queues the dispatcher queues work on, with the interval its work is due in
     *
     * @return array<string, int>
     */
    private static function queueIntervals(): array
    {
        $intervals = ['scheduler' => DispatchPollingWork::INTERVAL];

        foreach (Device::where('disabled', 0)->distinct()->pluck('poller_group')->map(intval(...)) as $group) {
            if (LibrenmsConfig::get('schedule_type.poller') == 'scheduler') {
                $intervals[PollDevice::queueFor($group)] = DispatchPollingWork::pollInterval();
            }
            if (LibrenmsConfig::get('schedule_type.discovery') == 'scheduler') {
                $intervals[DiscoverDevice::queueFor($group)] = DispatchPollingWork::discoveryInterval();
            }
        }

        return $intervals;
    }

    /**
     * @return string[]
     */
    private function serviceCommands(): array
    {
        $base_dir = rtrim(base_path(), '/');

        if (resolve(Binaries::class)->find('systemctl') === null) {
            return ["Run '$base_dir/artisan horizon' with your process supervisor"];
        }

        return [
            $base_dir === '/opt/librenms'
                ? 'sudo cp /opt/librenms/dist/librenms-worker.service /etc/systemd/system/'
                : "sudo sh -c 'sed \"s#/opt/librenms#$base_dir#\" $base_dir/dist/librenms-worker.service > /etc/systemd/system/librenms-worker.service'",
            'sudo systemctl enable --now librenms-worker.service',
        ];
    }
}
