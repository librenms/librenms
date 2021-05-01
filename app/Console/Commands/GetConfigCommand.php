<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\CompletesConfigArgument;
use App\Console\LnmsCommand;
use LibreNMS\Config;
use LibreNMS\Util\OS;
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

        // load os definition if requested, and remove special definition_loaded key
        if (preg_match('/^os\.(?<os>[^.]+)/', $setting, $matches)) {
            OS::loadDefinition($matches['os']);
            Config::forget("os.{$matches['os']}.definition_loaded");
        }

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
                $output = var_export($output, true);
            }

            $this->line($output);

            return 0;
        }

        return 1;
    }
}
