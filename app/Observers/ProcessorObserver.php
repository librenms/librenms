<?php

namespace App\Observers;

use App\Facades\LibrenmsConfig;
use App\Models\Eventlog;
use App\Models\Processor;
use LibreNMS\Enum\Severity;

class ProcessorObserver
{
    public function creating(Processor $processor): void
    {
        if ($processor->processor_perc_warn === null) {
            $processor->processor_perc_warn = LibrenmsConfig::get('processor_perc_warn', 75);
        }
    }

    public function created(Processor $processor): void
    {
        $message = "Processor Discovered: {$processor->processor_type} {$processor->processor_index} {$processor->processor_descr}";
        Eventlog::log($message, $processor->device_id, 'processors', Severity::Notice, $processor->processor_id);
    }

    public function deleted(Processor $processor): void
    {
        $message = "Processor Removed: {$processor->processor_type} {$processor->processor_index} {$processor->processor_descr}";
        Eventlog::log($message, $processor->device_id, 'processors', Severity::Notice, $processor->processor_id);
    }
}
