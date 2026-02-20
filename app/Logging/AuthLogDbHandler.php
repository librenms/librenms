<?php

namespace App\Logging;

use App\Models\AuthLog;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class AuthLogDbHandler extends AbstractProcessingHandler
{
    /**
     * @inheritDoc
     */
    protected function write(LogRecord $record): void
    {
        $log = new AuthLog($record->context);
        $log->result = $record->message;
        $log->save();
    }
}
