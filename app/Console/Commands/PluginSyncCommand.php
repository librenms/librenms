<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use LibreNMS\ComposerHelper;

class PluginSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plugin:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install all plugins added with plugin:add';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        return ComposerHelper::installPlugins();
    }
}
