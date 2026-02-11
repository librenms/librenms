<?php

namespace App\Console\Commands;

use App\Facades\DeviceCache;
use Illuminate\Console\Command;

class DeviceRemove extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'device:remove
                            {device spec : Hostname, IP, or device id to remove}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove a device';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $selector = $this->argument('device spec');

        $device = DeviceCache::get($selector);
        if (! $device->exists) {
            $this->fail(trans('commands.device:remove.doesnt_exists', ['device' => $selector]));
        }

        $device->delete();
    }
}
