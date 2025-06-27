<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\CompletesConfigArgument;
use App\Console\LnmsCommand;
use App\Facades\LibrenmsConfig;
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
        $this->addOption('dump');
    }

    public function handle(): int
    {
        $setting = $this->argument('setting');

        if ($this->option('dump')) {
            $this->line($setting ? json_encode(LibrenmsConfig::get($setting)) : LibrenmsConfig::toJson());

            return 0;
        }

        if (! $setting) {
            throw new \RuntimeException('Not enough arguments (missing: "setting").');
        }

        if (LibrenmsConfig::has($setting)) {
            $output = LibrenmsConfig::get($setting);
            if (! is_string($output)) {
                $output = json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }

            $this->line($output);

            return 0;
        }

        return 1;
    }
}
