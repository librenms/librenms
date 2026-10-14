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

        if ($this->option('reset') && ! $this->confirm(trans('commands.patch:remove.reset_prompt'))) {
            return 1;
        }

        if (empty($patches) && ! $this->option('reset')) {
            $this->info(trans('commands.patch:remove.none'));

            return 0;
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
            $manager->reset();
            $this->info(trans('commands.patch:remove.reset_done'));
            $failed = false;
        } elseif ($failed) {
            $this->line(trans('commands.patch:remove.reset_hint'));
        }

        $this->afterPatchChanges($removed);

        return $failed ? 1 : 0;
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
        foreach ($requested as $pr) {
            $patch = $manager->get((int) ltrim($pr, '#'));
            if ($patch === null) {
                $this->error(trans('commands.patch:remove.not_applied', ['pr' => $pr]));

                return null;
            }
            $patches[] = $patch;
        }

        // keep the order they were applied in
        return array_values(array_filter($manager->all(), fn (Patch $patch) => in_array($patch, $patches, true)));
    }

    /**
     * Undo migrations added by the patch before the migration files are removed
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

        if (! $this->confirm(trans('commands.patch:remove.rollback_prompt'), true)) {
            return false;
        }

        foreach ($ran->sortKeysDesc() as $name => $batch) {
            $this->callSilently('migrate:rollback', [
                '--path' => [base_path($migrations[$name])],
                '--realpath' => true,
                '--batch' => $batch,
                '--force' => true,
            ]);
        }

        return true;
    }
}
