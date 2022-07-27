<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\CompletesPluginArgument;
use App\Console\LnmsCommand;
use App\Models\Plugin;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\Console\Input\InputArgument;

class PluginEnable extends LnmsCommand
{
    use CompletesPluginArgument;

    protected $name = 'plugin:enable';


    /**
     * Create a new command instance.
     *
     * @return void
     */
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
            $query = Plugin::when($plugin !== 'all', function (Builder $query) use ($plugin) {
                $query->where('plugin_name', 'like', $this->argument('plugin'))
                    ->orderBy('version', 'DESC')
                    ->limit(1);
            });

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
