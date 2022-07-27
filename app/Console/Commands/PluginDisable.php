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

    public function handle(): int
    {
        try {
            $plugin = $this->argument('plugin');
            $query = Plugin::query();

            if ($plugin !== 'all') {
                $query->where('plugin_name', 'like', $plugin);
            }

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
