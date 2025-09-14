<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use LibreNMS\Util\Notifications;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Console\Input\InputOption;

class MaintenanceFetchRSS extends LnmsCommand
{
    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'maintenance:fetch-rss';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $lock = Cache::lock('notifications', 86000);
        if ($lock->get()) {
            Notifications::post();
            $lock->release();

            return 0;
        }

        return 1;
    }
}
