<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\HandlesPatchSideEffects;
use App\Console\LnmsCommand;
use App\Patches\Patch;
use App\Patches\PatchException;
use App\Patches\PatchManager;
use LibreNMS\Util\Git;
use Symfony\Component\Console\Input\InputArgument;

class PatchApply extends LnmsCommand
{
    use HandlesPatchSideEffects;

    protected $name = 'patch:apply';

    public function __construct()
    {
        parent::__construct();
        $this->addArgument('pull-request', InputArgument::REQUIRED | InputArgument::IS_ARRAY);
    }

    public function handle(PatchManager $manager): int
    {
        if (! Git::make()->binaryExists()) {
            $this->error(trans('commands.patch:apply.no_git'));

            return 1;
        }

        $failed = false;
        /** @var Patch[] $applied */
        $applied = [];

        foreach ($this->argument('pull-request') as $input) {
            $pr = Patch::parseNumber($input);
            if ($pr === null) {
                $this->error(trans('commands.patch:apply.invalid', ['pr' => $input]));
                $failed = true;
                continue;
            }

            if ($pr === 5926) {
                $this->error(trans('commands.patch:apply.example'));
                $failed = true;
                continue;
            }

            try {
                $this->line(trans('commands.patch:apply.fetching', ['pr' => $pr]));
                $patch = $manager->fetch($pr);

                $existing = $manager->get($pr);
                if ($patch->sha !== '' && $existing?->sha === $patch->sha && $existing->state === Patch::APPLIED && $manager->isApplied($existing)) {
                    $this->info(trans('commands.patch:apply.up_to_date', ['patch' => $patch->label(), 'sha' => $patch->shortSha()]));
                    continue;
                }

                foreach ($manager->apply($patch) as $warning) {
                    $this->warn($warning);
                }

                $key = $existing ? 'updated' : 'applied';
                $this->info(trans("commands.patch:apply.$key", ['patch' => $patch->label(), 'sha' => $patch->shortSha()]));
                $applied[] = $patch;
            } catch (PatchException $e) {
                $this->error($e->getMessage());
                $failed = true;
            }
        }

        if ($applied) {
            $this->afterPatchChanges($applied);
            $this->offerMigrations($applied);

            $prs = implode(' ', array_map(fn (Patch $patch) => $patch->pr, $applied));
            $this->newLine();
            $this->line(trans('commands.patch:apply.remove_hint', ['prs' => $prs]));
        }

        return $failed ? 1 : 0;
    }

    /**
     * @param  Patch[]  $patches
     */
    private function offerMigrations(array $patches): void
    {
        foreach ($patches as $patch) {
            if ($patch->migrations()) {
                $this->warn(trans('commands.patch:apply.migrations', ['pr' => $patch->pr]));
                if ($this->confirm(trans('commands.patch:apply.migrate_prompt'), true)) {
                    // run in a new process so the patched code is loaded
                    $this->runSubprocess(['php', base_path('lnms'), 'migrate', '--force', '--no-interaction']);
                }

                return;
            }
        }
    }
}
