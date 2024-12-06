<?php

namespace App\Listeners;

use Illuminate\Database\Events\StatementPrepared;

class LegacyQueryListener
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
     * set FETCH_ASSOC for queries that required by setting the global variable $PDO_FETCH_ASSOC (for dbFacile)
     *
     * @param  \Illuminate\Database\Events\StatementPrepared  $event
     * @return void
     */
    public function handle(StatementPrepared $event): void
    {
        global $PDO_FETCH_ASSOC;

        if ($PDO_FETCH_ASSOC) {
            $event->statement->setFetchMode(\PDO::FETCH_ASSOC);
        }
    }
}
