<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\HandlesPatchSideEffects;
use App\Console\LnmsCommand;
use App\Patches\Patch;
use App\Patches\PatchException;
use App\Patches\PatchManager;
use Illuminate\Support\Facades\DB;
use LibreNMS\Util\Git;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class PatchRemove extends LnmsCommand
{
    use HandlesPatchSideEffects;

    protected $name = 'patch:remove';

    public function __construct()
    {
        parent::__construct();
        $this->addArgument('pull-request', InputArgument::OPTIONAL | InputArgument::IS_ARRAY);
        $this->addOption('keep-migrations', null, InputOption::VALUE_NONE);
        $this->addOption('reset', null, InputOption::VALUE_NONE);
    }

    public function handle(PatchManager $manager): int
    {
        if (! Git::make()->binaryExists()) {
            $this->error(trans('commands.patch:apply.no_git'));

            return 1;
        }

        $patches = $this->selectPatches($manager);
        if ($patches === null) {
            return 1;
        }

        if ($this->option('reset')) {
            if (! $this->confirmReset($manager)) {
                return 1;
            }
        } elseif (empty($patches)) {
            $this->info(trans('commands.patch:remove.none'));

            return 0;
        } elseif (empty($this->argument('pull-request')) && ! $this->confirmRemoveAll($patches)) {
            return 1;
        }

        $failed = false;
        $removed = [];

        // newest first, so overlapping patches come off cleanly
        foreach (array_reverse($patches) as $patch) {
            $rolledBack = false;
            try {
                $rolledBack = $this->rollbackMigrations($patch);

                foreach ($manager->remove($patch) as $warning) {
                    $this->warn($warning);
                }

                $this->info(trans('commands.patch:remove.removed', ['patch' => $patch->label()]));
                $removed[] = $patch;
            } catch (PatchException $e) {
                $this->error($e->getMessage());
                if ($rolledBack) {
                    $this->warn(trans('commands.patch:remove.migrations_rerun'));
                }
                $failed = true;
            }
        }

        if ($this->option('reset')) {
            try {
                $manager->reset();
                $this->info(trans('commands.patch:remove.reset_done'));
                $failed = false;
            } catch (PatchException $e) {
                $this->error($e->getMessage());
                $failed = true;
            }
        } elseif ($failed) {
            $this->line(trans('commands.patch:remove.reset_hint'));
        }

        $this->afterPatchChanges($removed);

        return $failed ? 1 : 0;
    }

    /**
     * @param  Patch[]  $patches
     */
    private function confirmRemoveAll(array $patches): bool
    {
        $this->line(trans('commands.patch:remove.remove_all'));
        foreach ($patches as $patch) {
            $this->line('  ' . $patch->label());
        }

        if ($this->confirm(trans('commands.patch:remove.remove_all_prompt'))) {
            return true;
        }

        $this->line(trans('commands.patch:remove.remove_all_hint'));

        return false;
    }

    /**
     * Show exactly what will be discarded, reset is not limited to changes made by patches
     */
    private function confirmReset(PatchManager $manager): bool
    {
        try {
            $preview = $manager->resetPreview();
        } catch (PatchException $e) {
            $this->error($e->getMessage());

            return false;
        }

        $this->warn(trans('commands.patch:remove.reset_warning'));

        if (empty($preview['modified']) && empty($preview['untracked'])) {
            $this->line(trans('commands.patch:remove.reset_nothing'));
        }
        if ($preview['modified']) {
            $this->line(trans('commands.patch:remove.reset_modified'));
            foreach ($preview['modified'] as $file) {
                $this->line("  $file");
            }
        }
        if ($preview['untracked']) {
            $this->line(trans('commands.patch:remove.reset_untracked'));
            foreach ($preview['untracked'] as $file) {
                $this->line("  $file");
            }
        }

        return $this->confirm(trans('commands.patch:remove.reset_prompt'));
    }

    /**
     * @return Patch[]|null
     */
    private function selectPatches(PatchManager $manager): ?array
    {
        $requested = $this->argument('pull-request');
        if (empty($requested)) {
            return array_values($manager->all());
        }

        $patches = [];
        foreach ($requested as $input) {
            $pr = Patch::parseNumber($input);
            $patch = $pr === null ? null : $manager->get($pr);
            if ($patch === null) {
                $this->error(trans('commands.patch:remove.not_applied', ['pr' => $pr ?? $input]));

                return null;
            }
            $patches[] = $patch;
        }

        // keep the order they were applied in
        return array_values(array_filter($manager->all(), fn (Patch $patch) => in_array($patch, $patches, true)));
    }

    /**
     * Offer to undo migrations added by the patch before the migration files are removed.
     * Rolling back is never the default: down() may not restore the database to how it was.
     */
    private function rollbackMigrations(Patch $patch): bool
    {
        if ($this->option('keep-migrations') || ! $patch->migrations()) {
            return false;
        }

        $migrations = [];
        foreach ($patch->migrations() as $file) {
            $migrations[basename($file, '.php')] = $file;
        }

        try {
            $ran = DB::table('migrations')->whereIn('migration', array_keys($migrations))->pluck('batch', 'migration');
        } catch (\Exception) {
            // the database is not reachable, removing the patch is more important
            $this->warn(trans('commands.patch:remove.migrations_unknown', ['pr' => $patch->pr]));

            return false;
        }

        if ($ran->isEmpty()) {
            return false;
        }

        $this->warn(trans('commands.patch:remove.migrations', ['pr' => $patch->pr]));
        foreach ($ran->keys() as $name) {
            $this->line("  $name");
        }

        if ($patch->state !== Patch::APPLIED) {
            $this->warn(trans('commands.patch:remove.migrations_missing'));

            return false;
        }

        $this->warn(trans('commands.patch:remove.rollback_warning'));
        if (! $this->confirm(trans('commands.patch:remove.rollback_prompt'))) {
            $this->line(trans('commands.patch:remove.migrations_kept'));

            return false;
        }

        foreach ($ran->sortKeysDesc() as $name => $batch) {
            $result = $this->callSilently('migrate:rollback', [
                '--path' => [base_path($migrations[$name])],
                '--realpath' => true,
                '--batch' => $batch,
                '--force' => true,
            ]);

            if ($result !== 0) {
                throw new PatchException(trans('commands.patch:remove.rollback_failed', ['migration' => $name]));
            }
        }

        return true;
    }
}
