<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\CompletesPluginArgument;
use App\Console\LnmsCommand;
use App\Models\Plugin;
use Symfony\Component\Console\Input\InputArgument;

class PluginEnable extends LnmsCommand
{
    use CompletesPluginArgument;

    protected $name = 'plugin:enable';

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
                $query->where('plugin_name', 'like', $plugin)
                    ->limit(1)
                    ->orderBy('version', 'DESC');
            }

            $updated = $query->update(['plugin_active' => 1]);

            if ($updated == 0 && $query->exists()) {
                $this->info(trans('commands.plugin:enable.already_enabled'));

                return 0;
            }

            $this->info(trans_choice('commands.plugin:enable.enabled', $updated, ['count' => $updated]));

            return 0;
        } catch (\Exception $e) {
            $this->error(trans('commands.plugin:enable.failed'));

            return 1;
        }
    }
}
