<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Events\DevicePolled;
use App\Jobs\DispatchPollingWork;
use App\Jobs\PollDevice;
use App\Models\Device;
use App\Polling\Measure\MeasurementManager;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use LibreNMS\Config;
use LibreNMS\Polling\Result;
use LibreNMS\Util\Module;
use LibreNMS\Util\Version;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DevicePoll extends LnmsCommand
{
    protected $name = 'device:poll';
    private ?int $current_device_id = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->addArgument('device spec', InputArgument::REQUIRED);
        $this->addOption('modules', 'm', InputOption::VALUE_REQUIRED);
        $this->addOption('no-data', 'x', InputOption::VALUE_NONE);
        $this->addOption('dispatch', 'd', InputOption::VALUE_NONE);
    }

    public function handle(MeasurementManager $measurements): int
    {
        if ($this->option('dispatch')) {
            return $this->dispatchWork();
        }

        $this->configureOutputOptions();

        if ($this->option('no-data')) {
            Config::set('rrd.enable', false);
            Config::set('influxdb.enable', false);
            Config::set('influxdbv2.enable', false);
            Config::set('prometheus.enable', false);
            Config::set('graphite.enable', false);
        }

        try {
            if ($this->getOutput()->isVerbose()) {
                Log::debug(Version::get()->header());
                \LibreNMS\Util\OS::updateCache(true); // Force update of OS Cache
            }

            $module_overrides = Module::parseUserOverrides(explode(',', $this->option('modules') ?? ''));
            $this->printModules($module_overrides);

            $result = new Result;

            $this->line("Starting polling run:\n");

            // listen for the device polled events to mark the device completed
            Event::listen(function (DevicePolled $event) use ($result) {
                if ($event->device->device_id == $this->current_device_id) {
                    $result->markCompleted($event->device->status);
                }
            });

            foreach (Device::whereDeviceSpec($this->argument('device spec'))->pluck('device_id') as $device_id) {
                $this->current_device_id = $device_id;
                $result->markAttempted();
                PollDevice::dispatchSync($device_id, $module_overrides);
            }

            if ($result->hasAnyCompleted()) {
                if (! $this->output->isQuiet()) {
                    if ($result->hasMultipleCompleted()) {
                        $this->output->newLine();
                        $time_spent = sprintf('%0.3fs', $measurements->getCategory('device')->getSummary('poll')->getDuration());
                        $this->line(trans('commands.device:poll.polled', ['count' => $result->getCompleted(), 'time' => $time_spent]));
                    }
                    $this->output->newLine();
                    $measurements->printStats();
                }

                return 0;
            }

            // polled 0 devices, maybe there were none to poll
            if ($result->hasNoAttempts()) {
                $this->error(trans('commands.device:poll.errors.no_devices'));

                return 1;
            }

            // attempted some devices, but none were up.
            if ($result->hasNoCompleted()) {
                $this->line('<fg=red>' . trans_choice('commands.device:poll.errors.none_up', $result->getAttempted()) . '</>');

                return 6;
            }
        } catch (QueryException $e) {
            if ($e->getCode() == 2002) {
                $this->error(trans('commands.device:poll.errors.db_connect'));

                return 1;
            } elseif ($e->getCode() == 1045) {
                // auth failed, don't need to include the query
                $this->error(trans('commands.device:poll.errors.db_auth', ['error' => $e->getPrevious()->getMessage()]));

                return 1;
            }

            $this->error($e->getMessage());

            return 1;
        }

        $this->error(trans('commands.device:poll.errors.none_polled'));

        return 1; // failed to poll
    }

    private function dispatchWork(): int
    {
        $enabled = Config::get('scheduler.poll.enabled');

        if (! Config::get('scheduler.poll.enabled')) {
            $this->error('Scheduler based polling is disabled');

            return 1;
        }

        if ($this->argument('device spec') !== 'all') {
            $this->error('Dispatch only supports all devices');

            return 1;
        }

        $this->line('Dispatching polling work... press ctrl-c to cancel');
        while (true) {
            $this->output->write('.');
            DispatchPollingWork::dispatchSync(); // just do the dispatch work in this process
            sleep(10);
        }
    }

    private function printModules(array $module_overrides): void
    {
        if (! empty($module_overrides)) {
            $modules = array_map(function ($module, $status) {
                return $module . (is_array($status) ? '(' . implode(',', $status) . ')' : '');
            }, array_keys($module_overrides), array_values($module_overrides));

            Log::debug('Override poller modules: ' . implode(', ', $modules));
        }
    }
}
