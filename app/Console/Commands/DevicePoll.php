<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\ProcessesDevices;
use App\Console\LnmsCommand;
use App\Events\DevicePolled;
use App\Facades\LibrenmsConfig;
use App\Jobs\PollDevice;
use App\Models\Device;
use App\PerDeviceProcess;
use App\Polling\Measure\MeasurementManager;
use Illuminate\Database\QueryException;
use LibreNMS\Enum\ProcessType;
use LibreNMS\Util\ModuleList;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DevicePoll extends LnmsCommand
{
    use ProcessesDevices;

    protected $name = 'device:poll';
    protected ProcessType $processType = ProcessType::Poller;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->addArgument('device spec', InputArgument::REQUIRED);
        $this->addOption('modules', 'm', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY);
        $this->addOption('os', null, InputOption::VALUE_REQUIRED);
        $this->addOption('type', null, InputOption::VALUE_REQUIRED);
        $this->addOption('no-data', 'x', InputOption::VALUE_NONE);
        $this->addOption('dispatch', null, InputOption::VALUE_NONE);
    }

    public function handle(MeasurementManager $measurements): int
    {
        if ($this->option('dispatch')) {
            return $this->dispatchWork();
        }

        if ($this->option('no-data')) {
            LibrenmsConfig::set('rrd.enable', false);
            LibrenmsConfig::set('influxdb.enable', false);
            LibrenmsConfig::set('influxdbv2.enable', false);
            LibrenmsConfig::set('prometheus.enable', false);
            LibrenmsConfig::set('graphite.enable', false);
            LibrenmsConfig::set('kafka.enable', false);
        }

        try {
            $this->handleDebug();

            $processor = new PerDeviceProcess(
                $this->processType,
                $this->argument('device spec'),
                PollDevice::class,
                DevicePolled::class,
                ModuleList::fromUserOverrides($this->option('modules')),
                $this->option('os'),
                $this->option('type')
            );

            $this->line(__('commands.device:poll.starting'));
            $this->newLine();

            $processor->run();

            return $processor->processResults($measurements, $this->getOutput());
        } catch (QueryException $e) {
            return $this->handleQueryException($e);
        }
    }

    private function dispatchWork(): int
    {
        $modules = ModuleList::fromUserOverrides($this->option('modules'));
        $devices = Device::whereDeviceSpec($this->argument('device spec'))->pluck('device_id');

        if (\config('queue.default') == 'sync') {
            $this->error('Queue driver is sync, work will run in process.');
            sleep(1);
        }

        foreach ($devices as $device_id) {
            PollDevice::dispatch($device_id, $modules);
        }

        $this->line('Submitted work for ' . $devices->count() . ' devices');

        return 0;
    }
}
