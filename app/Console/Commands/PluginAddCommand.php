<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use LibreNMS\ComposerHelper;

class PluginAddCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plugin:add {package} {version?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install a plugin';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        // Add package to "real" composer.json first, to catch dependency failures etc.
        if (ComposerHelper::addPackage($this->argument('package'), $this->argument('version')) == 0) {
            return ComposerHelper::addPlugin($this->argument('package'), $this->argument('version'));
        }

        return 1;
    }
}
