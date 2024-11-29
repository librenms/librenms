<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Models\Device;
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
        $devices = Device::whereDeviceSpec($spec)->get();

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
