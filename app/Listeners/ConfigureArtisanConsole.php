<?php

namespace App\Listeners;

use Illuminate\Console\Events\ArtisanStarting;
use LibreNMS\Util\Version;

class ConfigureArtisanConsole
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ArtisanStarting $event): void
    {
        $console = $event->artisan;
        $console->setName('LibreNMS');
        $console->setVersion(Version::VERSION);
    }
}
