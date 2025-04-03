<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Models\Device;
use LibreNMS\Config;
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
        $this->addOption('json', 'j', InputOption::VALUE_NONE);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {


        return 0;
    }
}
