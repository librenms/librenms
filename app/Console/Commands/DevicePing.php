<?php

namespace App\Console\Commands;

use App\Actions\Device\DeviceIsPingable;
use App\Console\LnmsCommand;
use App\Facades\LibrenmsConfig;
use App\Jobs\PingCheck;
use App\Models\Device;
use Illuminate\Support\Arr;
use LibreNMS\Data\Source\Fping;
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
        $this->addOption('force', 'f', InputOption::VALUE_NONE);
        $this->addOption('groups', 'g', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(DeviceIsPingable $deviceIsPingable): int
    {
        $spec = $this->argument('device spec');

        if ($spec == 'fast') {
            // Check if we want to run pings through the dispatcher
            if (! $this->option('force') && ! Fping::runPing('dispatcher')) {
                $this->info('Fast Pings are not enabled for dispatcher scheduling. Add -f to the command to run manually, or make sure the icmp_check option is set to true and the schedule_type.ping option is set to dispatcher to allow dispatcher scheduling');

                return 0;
            }

            try {
                $groups = Arr::wrap($this->option('groups'));
                PingCheck::dispatchSync(($this->option('force') ? 'force' : 'dispatcher'), $groups);

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
