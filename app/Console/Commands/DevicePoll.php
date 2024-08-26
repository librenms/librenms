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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use LibreNMS\Config;
use LibreNMS\Polling\Result;
use LibreNMS\Util\Module;
use LibreNMS\Util\Version;
use LibreNMS\Enum\ProcessType;
use LibreNMS\Util\ModuleList;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DevicePoll extends LnmsCommand
{
    use ProcessesDevices;

    protected $name = 'device:poll';
    protected ProcessType $processType = ProcessType::poller;

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
        $this->addOption('no-data', 'x', InputOption::VALUE_NONE);
        $this->addOption('dispatch', null, InputOption::VALUE_NONE);
    }

    public function handle(MeasurementManager $measurements): int
    {
        if ($this->argument('device spec') == '-') {
            $stdin = fopen('php://stdin', 'r');
            $job_args = [];
            $temp_args = array_filter($this->options());
            array_walk($temp_args, function ($val, $opt) use (&$job_args) {
                if ($opt !== 'verbose') {
                    $job_args["--$opt"] = $val;
                }
            });

            $verbosity = $this->getOutput()->getVerbosity();
            if ($verbosity >= 256) {
                $job_args['-vvv'] = true;
            } elseif ($verbosity >= 128) {
                $job_args['-vv'] = true;
            } elseif ($verbosity >= 64) {
                $job_args['-v'] = true;
            }

            while ($line = trim(fgets($stdin))) {
                $job_args['device spec'] = $line;
                Artisan::call('device:poll', $job_args);
            }

            fclose($stdin);

            return 0;
        }

        $this->configureOutputOptions();

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
        $module_overrides = Module::parseUserOverrides(explode(',', $this->option('modules') ?? ''));
        $devices = Device::whereDeviceSpec($this->argument('device spec'))->select('device_id', 'poller_group')->get();

        if (\config('queue.default') == 'sync') {
            $this->error('Queue driver is sync, work will run in process.');
            sleep(1);
        }

        foreach ($devices as $device) {
            Log::debug('Submitted work for device ID ' . $device['device_id'] . ' to queue poller-' . $device['poller_group']);
            PollDevice::dispatch($device['device_id'], $module_overrides)->onQueue('poller-' . $device['poller_group']);
        }

        $this->line('Submitted work for ' . $devices->count() . ' devices');

        return 0;
    }
}
