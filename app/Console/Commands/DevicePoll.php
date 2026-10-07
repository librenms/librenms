<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\CompletesDeviceArgument;
use App\Console\Commands\Traits\ProcessesDevices;
use App\Console\LnmsCommand;
use App\Events\DevicePolled;
use App\Facades\LibrenmsConfig;
use App\Jobs\DispatchPollingWork;
use App\Jobs\PollDevice;
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
    use CompletesDeviceArgument;

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
        if ($this->argument('device spec') !== 'all') {
            $this->error('Dispatch only supports all devices');

            return 1;
        }

        $this->line('Dispatching polling work... press ctrl-c to cancel');
        while (true) {  // @phpstan-ignore while.alwaysTrue (keep dispatching until ctrl-c)
            $this->output->write('.');
            DispatchPollingWork::dispatchSync(); // just do the dispatch work in this process
            sleep(10);
        }
    }
}
