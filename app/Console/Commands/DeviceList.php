<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Models\Device;
use Symfony\Component\Console\Input\InputOption;

class DeviceList extends LnmsCommand
{
    protected $name = 'device:list';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->addOption('apps', 'a', InputOption::VALUE_NONE);
        $this->addOption('ports', 'p', InputOption::VALUE_NONE);
        $this->addOption('ip', 'i', InputOption::VALUE_NONE);
        $this->addOption('storage', 's', InputOption::VALUE_NONE);
        $this->addOption('sensors', 'S', InputOption::VALUE_NONE);
        $this->addOption('device-per-line', 'l', InputOption::VALUE_NONE);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $devices = Device::when($this->option('apps'), fn ($q) => $q->with('applications'))
            ->when($this->option('ports'), fn ($q) => $q->with(['ports', 'ports.ipv4', 'ports.ipv6']))
            ->when($this->option('storage'), fn ($q) => $q->with('storage'))
            ->when($this->option('storage'), fn ($q) => $q->with('sensors'))
            ->get();

        if ($this->option('device-per-line')) {
            foreach ($devices as $device) {
                echo json_encode($device) . "\n";
            }

            return 0;
        }

        echo json_encode($devices) . "\n";

        return 0;
    }
}
