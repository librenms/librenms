<?php

namespace App\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use LibreNMS\Util\Debug;
use Log;

class QueryDebugListener
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
     * @param  \Illuminate\Database\Events\QueryExecuted  $query
     * @return void
     */
    public function handle(QueryExecuted $query): void
    {
        if (Debug::queryDebugIsEnabled()) {
            // collect bindings and make them a little more readable
            $bindings = collect($query->bindings)->map(function ($item) {
                if ($item instanceof \Carbon\Carbon) {
                    return $item->toDateTimeString();
                }

                return $item;
            })->toJson();

            Log::debug("SQL[%Y{$query->sql} %y$bindings%n {$query->time}ms] \n", ['color' => true]);
        }
    }
}
