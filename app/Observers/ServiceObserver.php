<?php

namespace App\Observers;

use App\Models\Service;

class ServiceObserver
{
    /**
     * Handle the service "created" event.
     *
     * @param  Service  $service
     * @return void
     */
    public function created(Service $service): void
    {
        //
    }

    /**
     * Handle the service "updated" event.
     *
     * @param  Service  $service
     * @return void
     */
    public function updated(Service $service): void
    {
        //
    }

    /**
     * Handle the service "deleted" event.
     *
     * @param  Service  $service
     * @return void
     */
    public function deleted(Service $service): void
    {
        //
    }

    /**
     * Handle the service "restored" event.
     *
     * @param  Service  $service
     * @return void
     */
    public function restored(Service $service): void
    {
        //
    }

    /**
     * Handle the service "force deleted" event.
     *
     * @param  Service  $service
     * @return void
     */
    public function forceDeleted(Service $service): void
    {
        //
    }
}
