<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
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

        $poller = new Poller($this->argument('device spec'), explode(',', $this->option('modules')), $this->output);

        $poller->poll();

        return 0;
    }
}
