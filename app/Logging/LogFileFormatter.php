<?php

namespace App\Logging;

class LogFileFormatter extends NoColorFormatter
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
}
