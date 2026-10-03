<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Jobs\PingCheck;
use App\Models\Device;
use Illuminate\Support\Arr;
use LibreNMS\Data\Source\Icmp\FpingResponse;
use LibreNMS\Polling\Method\Methods\IcmpPollingMethod;
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
    public function handle(IcmpPollingMethod $icmpMethod): int
    {
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

        /** @var Device $device */
        foreach ($devices as $device) {
            // ping even if icmp is disabled, this is an explicit user action
            $result = $icmpMethod->probe($device, $device->polling()->icmp());
            $icmpMethod->onProbeComplete($device, $result);

            /** @var FpingResponse $response */
            $response = $result->stat('fping_status');
            $this->line($device->displayName() . ' : ' . ($response->wasSkipped() ? 'skipped' : $response));
        }

        return 0;
    }
}
