<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Patches\Patch;
use App\Patches\PatchManager;
use Symfony\Component\Console\Input\InputOption;

class PatchList extends LnmsCommand
{
    protected $name = 'patch:list';

    public function __construct()
    {
        parent::__construct();
        $this->addOption('offline', null, InputOption::VALUE_NONE);
    }

    public function handle(PatchManager $manager): int
    {
        $patches = $manager->all();

        if (empty($patches)) {
            $this->info(trans('commands.patch:remove.none'));

            return 0;
        }

        $rows = array_map(fn (Patch $patch) => [
            $patch->pr,
            $patch->title,
            $patch->author,
            $patch->shortSha(),
            $patch->appliedAt,
            $this->state($manager, $patch),
            $this->option('offline') ? '' : $this->upstreamStatus($manager, $patch),
        ], array_values($patches));

        $this->table(trans('commands.patch:list.headers'), $rows);

        return 0;
    }

    private function state(PatchManager $manager, Patch $patch): string
    {
        if ($patch->state === Patch::SUSPENDED) {
            return trans('commands.patch:list.suspended');
        }

        // check the changes are still in place
        return $manager->isApplied($patch)
            ? trans('commands.patch:list.applied')
            : trans('commands.patch:list.modified');
    }

    private function upstreamStatus(PatchManager $manager, Patch $patch): string
    {
        $info = $manager->pullRequestInfo($patch->pr);

        if ($info === null) {
            return '?';
        }

        if (! empty($info['merged'])) {
            return trans('commands.patch:list.merged');
        }

        if (($info['state'] ?? '') === 'closed') {
            return trans('commands.patch:list.closed');
        }

        return ($info['head']['sha'] ?? $patch->sha) === $patch->sha
            ? trans('commands.patch:list.current')
            : trans('commands.patch:list.outdated');
    }
}
