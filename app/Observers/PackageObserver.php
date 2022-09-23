<?php

namespace App\Observers;

use App\Models\Package;
use Log;

class PackageObserver
{
    /**
     * Handle the Package "created" event.
     *
     * @param  \App\Models\Package  $package
     * @return void
     */
    public function created(Package $package)
    {
        Log::event('Package installed: ' . $package, $package->device_id, 'package', 3);
        Log::info("+ $package");
    }

    /**
     * Handle the Package "updated" event.
     *
     * @param  \App\Models\Package  $package
     * @return void
     */
    public function updated(Package $package)
    {
        if ($package->getOriginal('version') !== $package->version || $package->getOriginal('build') !== $package->build) {
            $message = $package . ' from ' . $package->getOriginal('version') . ($package->getOriginal('build') ? '-' . $package->getOriginal('build') : '');
            Log::event('Package updated: ' . $message, $package->device_id, 'package', 3);
            Log::info("u $message");
        }
    }

    /**
     * Handle the Package "deleted" event.
     *
     * @param  \App\Models\Package  $package
     * @return void
     */
    public function deleted(Package $package)
    {
        Log::event('Package removed: ' . $package, $package->device_id, 'package', 3);
        Log::info("- $package");
    }

    /**
     * Handle the Package "restored" event.
     *
     * @param  \App\Models\Package  $package
     * @return void
     */
    public function restored(Package $package)
    {
        //
    }

    /**
     * Handle the Package "force deleted" event.
     *
     * @param  \App\Models\Package  $package
     * @return void
     */
    public function forceDeleted(Package $package)
    {
        //
    }
}
