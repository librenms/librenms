<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\CompletesConfigArgument;
use App\Console\LnmsCommand;
use LibreNMS\Config;
use Symfony\Component\Console\Input\InputArgument;

class GetConfigCommand extends LnmsCommand
{
    use CompletesConfigArgument;

    protected $name = 'config:get';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->addArgument('setting', InputArgument::OPTIONAL);
        $this->addOption('json');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $setting = $this->argument('setting');
        if ($this->option('json')) {
            $this->line($setting ? json_encode(Config::get($setting)) : Config::toJson());

            return 0;
        }

        if (! $setting) {
            throw new \RuntimeException('Not enough arguments (missing: "setting").');
        }

        if (Config::has($setting)) {
            $output = Config::get($setting);
            if (! is_string($output)) {
                $output = var_export($output, 1);
            }

            $this->line($output);

            return 0;
        }

        return 1;
    }
}
