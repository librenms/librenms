<?php

namespace App\Console\Commands\Traits;

use App\Facades\LibrenmsConfig;
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
