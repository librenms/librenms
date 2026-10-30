<?php

namespace LibreNMS\Validations\Poller;

use App\Models\PollerCluster;
use App\Queue\QueueStatus;
use App\Queue\WorkerNode;
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
            $intervals = QueueStatus::pollingQueues();
            $worked = $status->workedQueues(array_keys($intervals));

            if (empty($worked)) {
                return ValidationResult::fail(trans('validation.validations.poller.CheckQueueWorkers.not_running'))
                    ->setFix($this->serviceCommands());
            }

            $inactive = PollerCluster::isQueueWorker()->isInactive()->pluck('poller_name');
            if ($inactive->isNotEmpty()) {
                return ValidationResult::fail(trans('validation.validations.poller.CheckQueueWorkers.nodes_down'))
                    ->setList('Inactive Nodes', $inactive->all());
            }

            $unworked = array_diff(array_keys($intervals), $worked);
            if (! empty($unworked)) {
                return ValidationResult::fail(trans('validation.validations.poller.CheckQueueWorkers.unworked'))
                    ->setList('Queues', array_values($unworked));
            }

            // jobs are queued when due, one waiting longer than the interval is not run before the next is due
            $behind = $status->queues(collect(), $intervals)
                ->filter(fn (array $queue): bool => isset($intervals[$queue['name']]) && $queue['age'] > $intervals[$queue['name']])
                ->map(fn (array $queue): string => "{$queue['name']} ({$queue['age']}s)");
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
