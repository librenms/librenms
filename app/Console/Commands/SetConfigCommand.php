<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use LibreNMS\DB\Eloquent;
use Symfony\Component\Console\Input\InputArgument;

class SetConfigCommand extends LnmsCommand
{
    protected $name = 'config:set';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->addArgument('setting', InputArgument::REQUIRED);
        $this->addArgument('value', InputArgument::OPTIONAL);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $setting = $this->argument('setting');
        $value = $this->argument('value');

        if (!$value) {
            if ($this->confirm(__('Unset :setting?', ['setting' => $setting]))) {
                \App\Models\Config::query()->where('config_name', $setting)->delete();
                return 0;
            }
            return 3;
        }

        if (!Eloquent::isConnected()) {
            $this->error(__('Database is not connected'));
            return 1;
        }

        \LibreNMS\Config::set($setting, $value, true);

        return 0;
    }
}
