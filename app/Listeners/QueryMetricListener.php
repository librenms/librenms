<?php

namespace App\Listeners;

use App\Polling\Measure\Measurement;
use App\Polling\Measure\MeasurementManager;
use Illuminate\Database\Events\QueryExecuted;

class QueryMetricListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \Illuminate\Database\Events\QueryExecuted  $event
     * @return void
     */
    public function handle(QueryExecuted $event): void
    {
        $type = strtolower(substr($event->sql, 0, strpos($event->sql, ' ')));
        app(MeasurementManager::class)->recordDb(Measurement::make($type, $event->time ? $event->time / 100 : 0));
    }
}
