<?php

namespace App\Logging;

class LogFileFormatter extends \Monolog\Formatter\LineFormatter
{
    public function __construct()
    {
        parent::__construct(
            "[%datetime%][%level_name%] %message% %context% %extra%\n",
            'Y-m-d\TH:i:s',
            true,
            true
        );
    }

    public function format(\Monolog\LogRecord $record): string
    {
        return parent::format($record);
    }
}
