<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Facades\LibrenmsConfig;
use App\Jobs\PingCheck;
use App\Models\Device;
use Illuminate\Support\Arr;
use LibreNMS\Polling\ConnectivityHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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
        $this->addOption('groups', 'g', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->configureOutputOptions();
        $spec = $this->argument('device spec');

        if ($spec == 'fast') {
            try {
                $groups = Arr::wrap($this->option('groups'));
                PingCheck::dispatchSync($groups);

                return 0;
            } catch (\Throwable $e) {
                $this->error($e->getMessage());

                return 1;
            }
       }

        if ($this->option('groups')) {
            $this->error('The --groups (-g) option is only supported with "fast" device spec.');

            return 1;
        }

        $devices = Device::whereDeviceSpec($spec)->get();

        if ($devices->isEmpty()) {
            $devices = [new Device(['hostname' => $spec])];
        }

        LibrenmsConfig::set('icmp_check', true); // ignore icmp disabled, this is an explicit user action

        /** @var Device $device */
        foreach ($devices as $device) {
            $helper = new ConnectivityHelper($device);
            $response = $helper->isPingable();

            $this->line($device->displayName() . ' : ' . ($response->wasSkipped() ? 'skipped' : $response));
        }

        return 0;
    }
}
