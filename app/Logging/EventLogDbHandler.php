<?php

namespace App\Logging;

use App\Models\Eventlog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use LibreNMS\Enum\Severity;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

/**
 * Stores records sent to the event log channel in the eventlog table.
 */
class EventLogDbHandler extends AbstractProcessingHandler
{
    /**
     * @inheritDoc
     */
    protected function write(LogRecord $record): void
    {
        $log = new Eventlog;
        $log->device_id = $record->context['device_id'] ?? null;
        $log->type = $record->context['type'] ?? null;
        $log->reference = $record->context['reference'] ?? null;
        $log->severity = $record->context['severity'] ?? Severity::Info;
        $log->message = $record->message;
        $log->datetime = Date::now();
        $log->username = Auth::check() ? Auth::user()->username : '';
        $log->save();
    }
}
