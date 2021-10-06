<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Models\Device;
use Illuminate\Database\Eloquent\Builder;
use LibreNMS\Config;
use LibreNMS\Polling\ConnectivityHelper;
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
    public function handle(): int
    {
        $spec = $this->argument('device spec');
        $devices = Device::query()->when($spec !== 'all', function (Builder $query) use ($spec) {
            /** @phpstan-var Builder<Device> $query */
            return $query->where('device_id', $spec)
                ->orWhere('hostname', $spec)
                ->limit(1);
        })->get();

        if ($devices->isEmpty()) {
            $devices = [new Device(['hostname' => $spec])];
        }

        Config::set('icmp_check', true); // ignore icmp disabled, this is an explicit user action

        /** @var Device $device */
        foreach ($devices as $device) {
            $helper = new ConnectivityHelper($device);
            $response = $helper->isPingable();

            $this->line($device->displayName() . ' : ' . ($response->wasSkipped() ? 'skipped' : $response));
        }

        return 0;
    }
}
