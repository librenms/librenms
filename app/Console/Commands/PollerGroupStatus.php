<?php

namespace App\Console\Commands;

use App\Actions\Device\DeviceIsPingable;
use App\Actions\Device\DeviceIsSnmpable;
use App\Console\LnmsCommand;
use App\Models\Device;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class PollerGroupStatus extends LnmsCommand
{
    protected $name = 'poller-group:status';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->addArgument('poller group', InputArgument::REQUIRED);
        $this->addOption('disable-icmp', 'i', InputOption::VALUE_NONE);
        $this->addOption('disable-snmp', 's', InputOption::VALUE_NONE);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $poller_group = $this->argument('poller group');
        $devices = Device::isActive()->where('poller_group', $poller_group)->get();
        if ($devices->isEmpty()) {
            $this->error('No devices found in poller group ' . $poller_group);

            return 1;
        }

        echo "Device ID|Hostname|ICMP|SNMP\n";
        /** @var Device $device */
        foreach ($devices as $device) {
            $icmp_response = app(DeviceIsPingable::class)->execute($device)->success();
            $snmp_response = app(DeviceIsSnmpable::class)->execute($device);
            $icmp = 'down';
            if ($icmp_response == true) {
                $icmp = 'up';
            }
            $snmp = 'down';
            if ($snmp_response == true) {
                $snmp = 'up';
            }

            $this->line("{$device->device_id}|{$device->displayName()}|$icmp|$snmp");
        }

        return 0;
    }
}
