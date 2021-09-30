<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Models\Device;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use LibreNMS\Polling\PollerHelper;
use Symfony\Component\Console\Input\InputArgument;

class DevicePing extends LnmsCommand
{
    protected $name = 'device:ping';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->addArgument('device spec', InputArgument::REQUIRED);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $spec = $this->argument('device spec');
        $devices = Device::when($spec !== 'all', function (Builder $query) use ($spec) {
            return $query->where('device_id', $spec)
                ->orWhere('hostname', $spec)
                ->limit(1);
        })->get();

        /** @var Device $device */
        foreach ($devices as $device) {
            $helper = new PollerHelper($device);
            $response = $helper->isPingable();

            $device->perf()->saveMany(Arr::wrap($response->toModel()));
            \Log::debug('ping: ' . var_export($response->success()), [$response]);
        }

        return 0;
    }
}
