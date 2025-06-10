<?php

namespace App\Console\Commands\Traits;

use App\Facades\LibrenmsConfig;
use App\Polling\Measure\MeasurementManager;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use LibreNMS\Polling\Result;
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

    protected function processResults(Result $result, MeasurementManager $measurements): int
    {
        $type = $this->processType->verb(); // discover or poll
        $translation_prefix = 'commands.device:' . $type;

        if ($result->hasAnyCompleted()) {
            if (! $this->output->isQuiet()) {
                if ($result->hasMultipleCompleted()) {
                    $this->output->newLine();
                    $time_spent = sprintf('%0.3fs', $measurements->getCategory('device')->getSummary($type)->getDuration());
                    $this->line(__($translation_prefix . '.actioned', ['count' => $result->getCompleted(), 'time' => $time_spent]));
                }
                $this->output->newLine();
                $measurements->printStats();
            }

            return 0;
        }

        // 0 devices actioned, maybe there were none
        if ($result->hasNoAttempts()) {
            if ($this->argument('device spec') == 'new') {
                $this->line(__('commands.errors.no_new_devices'));

                return 0; // no new devices is normal
            }

            $this->error(__('commands.errors.no_devices'));

            return 1;
        }

        // attempted some devices, but none were up.
        if ($result->hasNoCompleted()) {
            $this->line('<fg=red>' . trans_choice($translation_prefix . '.errors.none_up', $result->getAttempted()) . '</>');

            return 6;
        }

        $this->error(__($translation_prefix . '.errors.none_actioned'));

        return 1; // failed
    }
}
