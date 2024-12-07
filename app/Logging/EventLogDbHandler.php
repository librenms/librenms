<?php

namespace App\Logging;

use App\Models\Eventlog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class EventLogDbHandler extends AbstractProcessingHandler
{

    /**
     * @inheritDoc
     */
    protected function write(LogRecord $record): void
    {
        $log = new Eventlog($record->context);
        $log->message = $record->message;
        $log->datetime = Date::now();
        $log->username = (class_exists('\Auth') && Auth::check()) ? Auth::user()->username : '';
        $log->save();
    }
}
