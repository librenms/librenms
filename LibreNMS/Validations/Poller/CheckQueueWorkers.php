<?php

namespace LibreNMS\Validations\Poller;

use App\Horizon\QueueStatus;
use App\Horizon\WorkerNode;
use App\Jobs\DiscoverDevice;
use App\Jobs\DispatchPollingWork;
use App\Jobs\PollDevice;
use App\Models\Device;
use App\Models\PollerCluster;
use Exception;
use LibreNMS\DB\Eloquent;
use LibreNMS\Interfaces\Validation;
use LibreNMS\Util\Binaries;
use LibreNMS\ValidationResult;

/**
 * Queue workers are running and work every queue polling work is dispatched to
 */
class CheckQueueWorkers implements Validation
{
    /**
     * @inheritDoc
     */
    public function validate(): ValidationResult
    {
        if (! QueueStatus::available()) {
            return ValidationResult::fail(trans('validation.validations.poller.CheckQueueWorkers.queue_driver', ['driver' => config('queue.default')]), 'Set QUEUE_CONNECTION=redis in .env');
        }

        try {
            $status = app(QueueStatus::class);

            if ($status->workers()->isEmpty()) {
                return ValidationResult::fail(trans('validation.validations.poller.CheckQueueWorkers.not_running'))
                    ->setFix($this->serviceCommands());
            }

            $inactive = PollerCluster::isQueueWorker()->isInactive()->pluck('poller_name');
            if ($inactive->isNotEmpty()) {
                return ValidationResult::fail(trans('validation.validations.poller.CheckQueueWorkers.nodes_down'))
                    ->setList('Inactive Nodes', $inactive->all());
            }

            $intervals = self::queueIntervals();
            $unworked = array_diff(array_keys($intervals), $status->workedQueues());
            if (! empty($unworked)) {
                return ValidationResult::fail(trans('validation.validations.poller.CheckQueueWorkers.unworked'))
                    ->setList('Queues', array_values($unworked));
            }

            // wait is Horizon's estimate of the time to clear the jobs that are due
            $behind = $status->queues(collect())
                ->filter(fn (array $queue): bool => isset($intervals[$queue['name']]) && $queue['wait'] > $intervals[$queue['name']])
                ->map(fn (array $queue): string => "{$queue['name']} ({$queue['wait']}s)");
            if ($behind->isNotEmpty()) {
                return ValidationResult::warn(trans('validation.validations.poller.CheckQueueWorkers.behind'))
                    ->setList('Queues', $behind->values()->all());
            }
        } catch (Exception $e) {
            return ValidationResult::fail(trans('validation.validations.poller.CheckQueueWorkers.unavailable', ['message' => $e->getMessage()]));
        }

        return ValidationResult::ok(trans('validation.validations.poller.CheckQueueWorkers.ok'));
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return Eloquent::isConnected() && WorkerNode::inUse();
    }

    /**
     * Queues polling work is dispatched to, with the interval its work is due in
     *
     * @return array<string, int>
     */
    private static function queueIntervals(): array
    {
        $intervals = ['scheduler' => DispatchPollingWork::INTERVAL];

        foreach (Device::where('disabled', 0)->distinct()->pluck('poller_group')->map(intval(...)) as $group) {
            if (WorkerNode::inUse('poller')) {
                $intervals[PollDevice::queueFor($group)] = DispatchPollingWork::pollInterval();
            }
            if (WorkerNode::inUse('discovery')) {
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
            return ["Run '$base_dir/lnms worker' with a process supervisor that starts it again when it exits"];
        }

        return [
            $base_dir === '/opt/librenms'
                ? 'sudo cp /opt/librenms/dist/librenms-worker.service /etc/systemd/system/'
                : "sudo sh -c 'sed \"s#/opt/librenms#$base_dir#\" $base_dir/dist/librenms-worker.service > /etc/systemd/system/librenms-worker.service'",
            'sudo systemctl enable --now librenms-worker.service',
        ];
    }
}
