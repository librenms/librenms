<?php

namespace App\Logging;

use App\Models\AuthLog;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

/**
 * Stores records sent to the auth log channel in the authlog table.
 */
class AuthLogDbHandler extends AbstractProcessingHandler
{
    /**
     * @inheritDoc
     */
    protected function write(LogRecord $record): void
    {
        $log = new AuthLog;
        $log->user = $record->context['user'] ?? '';
        $log->address = $record->context['address'] ?? '';
        $log->result = $record->message;
        $log->save();
    }
}
