<?php

namespace App\Console\Commands\Traits;

use App\Patches\Patch;
use LibreNMS\Util\Notifications;
use Symfony\Component\Process\Process;

trait HandlesPatchSideEffects
{
    /**
     * Clear caches, and offer to update dependencies if the changed patches touched them
     *
     * @param  Patch[]  $patches  patches that were applied or removed
     */
    protected function afterPatchChanges(array $patches): void
    {
        $this->callSilently('optimize:clear');

        foreach ($patches as $patch) {
            if ($patch->changesDependencies()) {
                if ($this->confirm(trans('commands.patch:apply.composer_prompt'), true)) {
                    $this->runSubprocess(['php', base_path('scripts/composer_wrapper.php'), 'install', '--no-dev']);
                }

                return;
            }
        }
    }

    /**
     * Show a notification in the web UI, the database may not be available during updates
     */
    protected function notify(string $title, string $message, int $severity): void
    {
        try {
            Notifications::create($title, $message, 'lnms ' . $this->getName(), $severity);
        } catch (\Exception) {
            // the message is in the daily.sh log
        }
    }

    protected function removeNotification(string $title): void
    {
        try {
            Notifications::remove($title);
        } catch (\Exception) {
        }
    }

    /**
     * @param  list<string>  $command
     */
    protected function runSubprocess(array $command): bool
    {
        $process = new Process($command, base_path(), timeout: 1800);
        $process->run(fn ($type, $buffer) => $this->output->write($buffer));

        return $process->isSuccessful();
    }
}
