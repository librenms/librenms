<?php

namespace App\Restify\Actions;

use App\Models\Alert;
use LibreNMS\Enum\AlertState;

class UnmuteAlertAction extends AlertStateAction
{
    public static $uriKey = 'unmute';

    protected function mutate(Alert $alert): void
    {
        $alert->state = AlertState::ACTIVE;
        $alert->open = 1;
    }

    protected function successMessage(): string
    {
        return 'Alert unmuted.';
    }
}
