<?php

namespace App\Listeners;


use Illuminate\Console\Events\CommandStarting;
use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\Console\Command\Command;

class HideArtisanCommands
{
    /**
     * Handle the event.
     */
    public function handle(CommandStarting $event): void
    {
        if (ARTISAN_BINARY !== 'lnms') {
            return;
        }

        if (!($event->command === 'list' || $event->command === null)) {
            return;
        }

        $commandsToHide = [
            'auth:clear-resets',
            'cache:clear',
            'cache:forget',
            'cache:prune-stale-tags',
            'channel:list',
            'clear-compiled',
            'completion',
            'config:cache',
            'config:publish',
            'config:show',
            'db:*',
            'docs',
            'down',
            'dusk',
            'dusk:*',
            'env',
            'env:decrypt',
            'env:encrypt',
            'event:*',
            'flare:test',
            'install:api',
            'install:broadcasting',
            'key:*',
            'lang:publish',
            'make:*',
            'migrate:*',
            'model:*',
            'optimize',
            'optimize:clear',
            'package:discover',
            'permission:*',
            'poller:*',
            'queue:*',
            'route:*',
            'schedule:*',
            'schema:*',
            'serve',
            'storage:*',
            'stub:publish',
            'test',
            'translation:generate',
            'ui',
            'ui:*',
            'up',
            'vendor:*',
            'view:*',
            'vue-i18n:generate',
            'webpush:vapid',
            'ziggy:generate',
        ];

        try {
            $kernel = app()->make(Kernel::class);
            $commands = $kernel->all();
            // Hide specific commands
            foreach ($commands as $name => $command) {
                if ($command instanceof Command && $this->shouldHideCommand($name, $commandsToHide)) {
                    $command->setHidden();
                }
            }
        } catch (\Exception $e) {
            // Log error but don't crash the command
            app()->make('log')->error('Failed to hide commands: ' . $e->getMessage());
        }
    }

    /**
     * Determine if a command should be hidden based on patterns.
     */
    protected function shouldHideCommand(string $commandName, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            // Exact match
            if ($pattern === $commandName) {
                return true;
            }

            // Wildcard pattern (e.g., "db:*")
            if (str_ends_with($pattern, '*') && str_starts_with($commandName, substr($pattern, 0, -1))) {
                return true;
            }
        }

        return false;
    }
}
