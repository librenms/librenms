<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\HandlesPatchSideEffects;
use App\Console\LnmsCommand;
use App\Patches\PatchManager;

/**
 * Used by daily.sh after updating: forgets merged test patches that patch:pre-update took out,
 * or restores them if the update did not happen.
 */
class PatchPostUpdate extends LnmsCommand
{
    use HandlesPatchSideEffects;

    protected $name = 'patch:post-update';
    protected $hidden = true;

    public function handle(PatchManager $manager): int
    {
        $patches = $manager->all();
        $failed = false;

        foreach ($manager->finishUpdate() as $pr => $result) {
            $message = trans("commands.patch:post-update.$result", ['patch' => $patches[$pr]->label(), 'pr' => $pr]);

            if ($result === PatchManager::FAILED) {
                $failed = true;
                $this->error($message);
                $this->notify(trans('commands.patch:post-update.failed_title', ['pr' => $pr]), $message, 2);
            } else {
                $this->info($message);
                if ($result === PatchManager::MERGED) {
                    $this->notify(trans('commands.patch:post-update.merged_title', ['pr' => $pr]), $message, 0);
                }
            }
        }

        return $failed ? 1 : 0;
    }
}
