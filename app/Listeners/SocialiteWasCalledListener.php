<?php

namespace App\Listeners;

use App\Facades\LibrenmsConfig;
use Log;

class SocialiteWasCalledListener
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
     * @param  \SocialiteProviders\Manager\SocialiteWasCalled  $event
     * @return void
     */
    public function handle(\SocialiteProviders\Manager\SocialiteWasCalled $event): void
    {
        foreach (LibrenmsConfig::get('auth.socialite.configs', []) as $provider => $config) {
            // Treat not set as "disabled"
            if (! isset($config['listener'])) {
                continue;
            }
            $listener = $config['listener'];

            if (class_exists($listener)) {
                (new $listener)->handle($event);

                return;
            }

            Log::error("Wrong value for auth.socialite.configs.$provider.listener set, class: '$listener' does not exist!");
        }
    }
}
