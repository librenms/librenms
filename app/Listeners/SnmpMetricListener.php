<?php

namespace App\Listeners;

use App\Events\SnmpQueryExecuted;
use App\Polling\Measure\Measurement;
use App\Polling\Measure\MeasurementManager;

class SnmpMetricListener
{
    /**
     * Handle the event.
     */
    public function handle(SnmpQueryExecuted $event): void
    {
        app(MeasurementManager::class)->recordSnmp(Measurement::make($event->method, $event->duration));
    }
}
