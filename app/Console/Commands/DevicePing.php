<?php

namespace App\Console\Commands;

use App\Actions\Device\DeviceIsPingable;
use App\Console\LnmsCommand;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
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
    public function handle(DeviceIsPingable $deviceIsPingable): int
    {
        $spec = $this->argument('device spec');
        $devices = Device::whereDeviceSpec($spec)->get();

        if ($devices->isEmpty()) {
            $devices = [new Device(['hostname' => $spec])];
        }

        LibrenmsConfig::set('icmp_check', true); // ignore icmp disabled, this is an explicit user action

        /** @var Device $device */
        foreach ($devices as $device) {
            $response = $deviceIsPingable->execute($device);

            $this->line($device->displayName() . ' : ' . ($response->wasSkipped() ? 'skipped' : $response));
        }

        return 0;
    }
}
