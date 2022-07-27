<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\CompletesPluginArgument;
use App\Console\LnmsCommand;
use App\Models\Plugin;
use Symfony\Component\Console\Input\InputArgument;

class PluginDisable extends LnmsCommand
{
    use CompletesPluginArgument;

    protected $name = 'plugin:disable';

    public function __construct()
    {
        parent::__construct();
        $this->addArgument('plugin', InputArgument::REQUIRED);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $plugin = $this->argument('plugin');
            $query = Plugin::when($plugin !== 'all', function ($query) use ($plugin) {
                $query->where('plugin_name', 'like', $this->argument('plugin'));
            });

            $updated = $query->update(['plugin_active' => 0]);

            if ($updated == 0 && $query->exists()) {
                $this->info(trans('commands.plugin:enable.already_enabled'));

                return 0;
            }

            $this->info(trans_choice('commands.plugin:disable.disabled', $updated, ['name' => $updated]));

            return 0;
        } catch (\Exception $e) {
            $this->error(trans('commands.plugin:disable.failed'));

            return 1;
        }
    }
}
