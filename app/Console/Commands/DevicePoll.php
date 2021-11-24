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
            Config::set('prometheus.enable', false);
            Config::set('graphite.enable', false);
        }

        try {
            $poller = app(Poller::class, ['device_spec' => $this->argument('device spec'), 'module_override' => explode(',', $this->option('modules'))]);
            $polled = $poller->poll();

            if ($polled > 0) {
                if (! $this->output->isQuiet()) {
                    if ($polled > 1) {
                        $this->output->newLine();
                        $this->line(sprintf('Polled %d devices in %0.3fs', $polled, $measurements->getCategory('device')->getSummary('poll')->getDuration()));
                    }
                    $this->output->newLine();
                    $measurements->printStats();
                }

                return 0;
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

        return 1; // failed to poll
    }
}
