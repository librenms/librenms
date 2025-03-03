<?php

namespace App\Listeners;

use App\Action;
use App\Actions\Alerts\RunAlertRulesAction;
use App\Events\DevicePolled;
use App\Facades\LibrenmsConfig;
use Log;

class CheckAlerts
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  DevicePolled  $event
     * @return void
     */
    public function handle(DevicePolled $event): void
    {
        if (LibrenmsConfig::get('alert.disable')) {
            return;
        }

        Log::info('#### Start Alerts ####');
        $start = microtime(true);

        Action::execute(RunAlertRulesAction::class, $event->device);

        $end = round(microtime(true) - $start, 4);
        Log::info("#### End Alerts ({$end}s) ####\n");
    }
}
