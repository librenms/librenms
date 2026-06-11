<?php

namespace App\Restify\Actions;

use App\Models\Alert;
use LibreNMS\Enum\AlertState;

class AcknowledgeAlertAction extends AlertStateAction
{
    public static $uriKey = 'acknowledge';

    protected function mutate(Alert $alert): void
    {
        $alert->state = AlertState::ACKNOWLEDGED;
        $alert->open = 0;
    }

    protected function successMessage(): string
    {
        return 'Alert acknowledged.';
    }
}
