<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\HandlesPatchSideEffects;
use App\Console\LnmsCommand;
use App\Patches\PatchManager;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Used by daily.sh before updating: reports test patches that will block the update.
 * Test patches are never removed automatically, the user must remove them with lnms patch:remove.
 */
class PatchPreUpdate extends LnmsCommand
{
    use HandlesPatchSideEffects;

    protected $name = 'patch:pre-update';
    protected $hidden = true;

    public function __construct()
    {
        parent::__construct();
        $this->addArgument('target', InputArgument::REQUIRED);
    }

    public function handle(PatchManager $manager): int
    {
        $patches = $manager->all();
        $target = $this->argument('target');

        if (! $manager->restoreComposerFiles()) {
            $this->line(trans('commands.patch:pre-update.composer_kept'));
        }

        $title = trans('commands.patch:pre-update.blocked_title');
        $blocking = $manager->blockingPatches($target);
        if (empty($blocking)) {
            $this->removeNotification($title);

            return 0;
        }

        $lines = [];
        foreach ($blocking as $pr => $files) {
            $reason = $manager->mergeCommit($patches[$pr], $target) ? 'merged' : 'conflict';
            $line = trans("commands.patch:pre-update.$reason", ['patch' => $patches[$pr]->label(), 'pr' => $pr, 'files' => implode(', ', $files)]);
            $this->error($line);
            $lines[] = $line;
        }

        $this->notify($title, trans('commands.patch:pre-update.blocked') . '<br>' . implode('<br>', $lines), 2);

        return 1;
    }
}
