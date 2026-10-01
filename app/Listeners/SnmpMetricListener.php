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
        $manager = app(MeasurementManager::class);
        $manager->recordSnmp(Measurement::make($event->method, $event->duration));
        $manager->record('snmp_backend', Measurement::make($event->backend, $event->duration));
    }
}
