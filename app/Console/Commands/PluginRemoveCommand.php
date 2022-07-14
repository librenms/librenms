<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use LibreNMS\ComposerHelper;

class PluginRemoveCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plugin:remove {package}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove an installed plugin';

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
        // Remove package from plugin first
        // in case of failure, daily.sh should then be able to cleanup and leftover mess.
        ComposerHelper::removePlugin($this->argument('package'));
        ComposerHelper::removePackage($this->argument('package'));

        return 0;
    }
}
