<?php

namespace App\Console\Commands\Traits;

use App\Facades\LibrenmsConfig;
use App\Jobs\DispatchPollingWork;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use LibreNMS\Util\Version;

trait ProcessesDevices
{
    protected function handleDebug(): void
    {
        if ($this->getOutput()->isVerbose()) {
            Log::debug(Version::get()->header());
            LibrenmsConfig::invalidateAndReload();
        }
    }

    /**
     * Dispatch queued work in a loop, for testing without the scheduler. Ignores the schedule_type settings.
     */
    protected function dispatchWork(bool $poll, bool $discover): int
    {
        if ($this->argument('device spec') !== 'all') {
            $this->error(__('commands.errors.dispatch_all_only'));

            return 1;
        }

        $this->line(__('commands.dispatching'));
        while (true) {  // @phpstan-ignore while.alwaysTrue (keep dispatching until ctrl-c)
            $this->output->write('.');
            DispatchPollingWork::dispatchSync(poll: $poll, discover: $discover); // dispatch in this process
            sleep(10);
        }
    }

    protected function handleQueryException(QueryException $e): int
    {
        if ($e->getCode() == 2002) {
            $this->error(__('commands.errors.db_connect'));

            return 1;
        } elseif ($e->getCode() == 1045) {
            // auth failed, don't need to include the query
            $this->error(__('commands.errors.db_auth', ['error' => $e->getPrevious()->getMessage()]));

            return 1;
        }

        $this->error($e->getMessage());

        return 1;
    }
}
