<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Horizon\WorkerNode;

class WorkerReload extends LnmsCommand
{
    protected $name = 'worker:reload';

    public function handle(): int
    {
        $pid = WorkerNode::runningPid();
        if ($pid === null) {
            $this->info(__('commands.worker:reload.not_running'));

            return 0;
        }

        posix_kill($pid, SIGHUP);
        $this->info(__('commands.worker:reload.reloading', ['pid' => $pid]));

        return 0;
    }
}
