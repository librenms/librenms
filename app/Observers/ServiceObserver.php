<?php

namespace App\Observers;

use App\Service;

class ServiceObserver
{
    /**
     * Handle the service "created" event.
     *
     * @param  \App\Service  $service
     * @return void
     */
    public function created(Service $service)
    {
        //
    }

    /**
     * Handle the service "updated" event.
     *
     * @param  \App\Service  $service
     * @return void
     */
    public function updated(Service $service)
    {
        //
    }

    /**
     * Handle the service "deleted" event.
     *
     * @param  \App\Service  $service
     * @return void
     */
    public function deleted(Service $service)
    {
        //
    }

    /**
     * Handle the service "restored" event.
     *
     * @param  \App\Service  $service
     * @return void
     */
    public function restored(Service $service)
    {
        //
    }

    /**
     * Handle the service "force deleted" event.
     *
     * @param  \App\Service  $service
     * @return void
     */
    public function forceDeleted(Service $service)
    {
        //
    }
}
