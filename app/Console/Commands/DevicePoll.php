<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Polling\Measure\MeasurementManager;
use Illuminate\Database\QueryException;
use LibreNMS\Config;
use LibreNMS\Poller;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DevicePoll extends LnmsCommand
{
    protected $name = 'device:poll';

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
    }

    public function handle(MeasurementManager $measurements): int
    {
        $this->configureOutputOptions();

        if ($this->option('no-data')) {
            Config::set('rrd.enable', false);
            Config::set('influxdb.enable', false);
            Config::set('influxdbv2.enable', false);
            Config::set('prometheus.enable', false);
            Config::set('graphite.enable', false);
        }

        try {
            /** @var \LibreNMS\Poller $poller */
            $poller = app(Poller::class, ['device_spec' => $this->argument('device spec'), 'module_override' => explode(',', $this->option('modules') ?? '')]);
            $result = $poller->poll();

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
}
