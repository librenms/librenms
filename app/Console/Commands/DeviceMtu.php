<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Jobs\MtuCheck;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DeviceMtu extends LnmsCommand
{
    protected $name = 'device:mtu';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->addOption('groups', 'g', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->configureOutputOptions();

        if (count($this->option('groups')) === 0) {
            return 1;
        }

        try {
            $groups = Arr::wrap($this->option('groups'));
            MtuCheck::dispatchSync($groups);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return 1;
        }

        return 0;
    }
}
