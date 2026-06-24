<?php

namespace App\Console\Commands;

use App\Actions\Device\DeviceIsPingable;
use App\Console\LnmsCommand;
use App\Facades\LibrenmsConfig;
use App\Jobs\PingCheck;
use App\Models\Device;
use Illuminate\Support\Arr;
use LibreNMS\Util\Debug;
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
        $this->addOption('scheduler', 'S', InputOption::VALUE_REQUIRED, 'The scheduler invoking this command');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(DeviceIsPingable $deviceIsPingable): int
    {
        $invokedScheduler = $this->option('scheduler');
        $configuredScheduler = LibrenmsConfig::get('schedule_type.ping');
        if ($invokedScheduler && $configuredScheduler !== 'legacy' && $invokedScheduler !== $configuredScheduler) {
            if (Debug::isEnabled() || $this->getOutput()->isVerbose()) {
                $this->info("Ping is not enabled for $invokedScheduler scheduling. (Configured: $configuredScheduler)");
            }

            return 0;
        }

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
            $response = $deviceIsPingable->execute($device);

            $this->line($device->displayName() . ' : ' . ($response->wasSkipped() ? 'skipped' : $response));
        }

        return 0;
    }
}
