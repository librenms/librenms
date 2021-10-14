<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
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

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('no-data')) {
            Config::set('rrd.enable', false);
            Config::set('influxdb.enable', false);
            Config::set('prometheus.enable', false);
            Config::set('graphite.enable', false);
        }

        try {
            $poller = new Poller($this->argument('device spec'), explode(',', $this->option('modules')), $this->output);
            $poller->poll();
        } catch (QueryException $e){
            if ($e->getCode() == 2002) {
                $this->error(trans('commands.device:poll.errors.db_connect'));
                return 1;
            } elseif($e->getCode() == 1045) {
                // auth failed, don't need to include the query
                $this->error(trans('commands.device:poll.errors.db_auth', ['error' => $e->getPrevious()->getMessage()]));
                return 1;
            }

            $this->error($e->getMessage());
            return 1;
        }

        return 0;
    }
}
