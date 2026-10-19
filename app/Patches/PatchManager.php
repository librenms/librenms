<?php

/**
 * PatchManager.php
 *
 * Apply GitHub pull requests to the local install for testing and remove them again
 * without touching any other local changes.
 *
 * The exact diff that was applied is saved so it can be reversed later. Patches are
 * kept in a stack (in the order they were applied) so a patch that overlaps a later
 * one can still be removed by temporarily unwinding the patches on top of it.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Patches;

use App\Facades\LibrenmsConfig;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use LibreNMS\Util\Http;
use Symfony\Component\Process\Process;

/**
 * @phpstan-import-type PatchArray from Patch
 * @phpstan-import-type PatchFile from Patch
 */
class PatchManager
{
    private const DIFF_URL = 'https://github.com/librenms/librenms/pull/%d.diff';
    private const RAW_URL = 'https://raw.githubusercontent.com/librenms/librenms/%s/%s';

    /** Directories cleaned of untracked files by reset() */
    private const RESET_DIRS = ['app/', 'bootstrap/', 'database/', 'doc/', 'html/', 'includes/', 'LibreNMS/', 'licenses/', 'misc/', 'mibs/', 'resources/', 'routes/', 'scripts/', 'tests/'];

    private readonly string $baseDir;
    private readonly string $storageDir;
    private readonly string $apiUrl;

    /** @var array<int, Patch>|null */
    private ?array $patches = null;

    public function __construct(
        ?string $baseDir = null,
        ?string $storageDir = null,
        ?string $apiUrl = null,
    ) {
        $this->baseDir = $baseDir ?? base_path();
        $this->storageDir = $storageDir ?? storage_path('app/patches');
        $this->apiUrl = rtrim($apiUrl ?? (string) LibrenmsConfig::get('github_api'), '/') . '/';
    }

    /**
     * Patches in the order they were applied
     *
     * @return array<int, Patch> keyed by pull request number
     */
    public function all(): array
    {
        if ($this->patches === null) {
            $this->patches = [];
            $index = $this->storageDir . '/patches.json';

            if (is_file($index)) {
                /** @var list<PatchArray> $records */
                $records = json_decode((string) file_get_contents($index), true) ?: [];
                foreach ($records as $record) {
                    $diffFile = $this->diffFile((int) $record['pr']);
                    if (is_file($diffFile)) {
                        $this->patches[(int) $record['pr']] = Patch::fromArray($record, (string) file_get_contents($diffFile));
                    }
                }
            }
        }

        return $this->patches;
    }

    public function get(int $pr): ?Patch
    {
        return $this->all()[$pr] ?? null;
    }

    /**
     * Download a pull request from GitHub and build the patch for it.
     * GitHub diffs do not contain binary files, those are downloaded and added to the patch.
     *
     * @throws PatchException
     */
    public function fetch(int $pr): Patch
    {
        $info = $this->pullRequestInfo($pr);
        $sha = $info['head']['sha'] ?? '';

        $response = $this->download(sprintf(self::DIFF_URL, $pr), "pull request #$pr");

        $diff = '';
        $files = [];
        foreach (preg_split('/^(?=diff --git )/m', $response, flags: PREG_SPLIT_NO_EMPTY) ?: [] as $section) {
            if (! str_starts_with($section, 'diff --git ')) {
                continue;
            }

            $file = $this->parseSection($section);
            $files[] = $file;
            $diff .= preg_match('/^Binary files .* differ$/m', $section)
                ? $this->binarySection($section, $file, $sha ?: "refs/pull/$pr/head")
                : $section;
        }

        if (trim($diff) === '') {
            throw new PatchException("Pull request #$pr has no changes");
        }

        return new Patch(
            pr: $pr,
            sha: $sha,
            diff: $diff,
            files: $files,
            title: $info['title'] ?? '',
            author: $info['user']['login'] ?? '',
        );
    }

    /**
     * Apply a patch to the working tree. If another version of the same pull request is
     * applied, it is replaced (and restored if the new version does not apply).
     *
     * @return list<string> warnings
     *
     * @throws PatchException
     */
    public function apply(Patch $patch): array
    {
        $warnings = [];
        $existing = $this->get($patch->pr);
        if ($existing) {
            $warnings = $this->unapply($existing);
        }

        $result = $this->gitApply($patch);
        if (! $result->isSuccessful()) {
            if ($existing?->state === Patch::APPLIED && ! $this->gitApply($existing)->isSuccessful()) {
                $existing->state = Patch::SUSPENDED;
                $this->save($existing);
                $warnings[] = "Previously applied version of #$patch->pr could not be restored";
            }

            throw new PatchException("Pull request #$patch->pr does not apply cleanly:\n" . trim($result->getErrorOutput()));
        }

        $patch->state = Patch::APPLIED;
        $patch->appliedAt = date('Y-m-d H:i:s');
        $this->forget($patch->pr); // re-add at the top of the stack
        $this->save($patch);

        return $warnings;
    }

    /**
     * Reverse exactly the changes made by the patch, leaving other local changes alone.
     *
     * @return list<string> warnings
     *
     * @throws PatchException
     */
    public function remove(Patch $patch): array
    {
        $warnings = $this->unapply($patch);
        $this->forget($patch->pr);

        return $warnings;
    }

    /**
     * Check if the changes from this patch are currently in the working tree,
     * including when patches applied after it changed the same lines
     */
    public function isApplied(Patch $patch): bool
    {
        return $this->simulate($this->unwindSteps($patch, $this->appliedAfter($patch))) === null;
    }

    /**
     * Applied patches that will prevent an update to $target, because the update changes files they changed
     *
     * @return array<int, list<string>> PR number => conflicting files
     */
    public function blockingPatches(string $target): array
    {
        $changed = explode("\n", trim($this->git(['diff', '--name-only', '--no-renames', 'HEAD', $target])->getOutput()));
        $blocking = [];

        foreach ($this->all() as $pr => $patch) {
            $files = array_values(array_intersect($patch->paths(), $changed));
            if ($patch->state === Patch::APPLIED && $files) {
                $blocking[$pr] = $files;
            }
        }

        return $blocking;
    }

    /**
     * Discard local changes to composer.json and composer.lock (made by installing plugins),
     * unless an applied patch changes them. Those changes block the update instead.
     *
     * @return bool false if the changes were kept
     */
    public function restoreComposerFiles(): bool
    {
        foreach ($this->all() as $patch) {
            if ($patch->state === Patch::APPLIED && $patch->changesDependencies()) {
                return false;
            }
        }

        $this->git(['checkout', '--quiet', '--', 'composer.json', 'composer.lock']);

        return true;
    }

    /**
     * Files that reset() will discard
     *
     * @return array{modified: list<string>, untracked: list<string>}
     *
     * @throws PatchException
     */
    public function resetPreview(): array
    {
        $this->ensureRepository();

        // staged new files become untracked when unstaged, so they are only removed inside RESET_DIRS
        $modified = $this->git(['diff', '--name-only', '--no-renames', '--diff-filter=a', 'HEAD'])->getOutput();
        $added = $this->git(['diff', '--name-only', '--no-renames', '--diff-filter=A', '--cached', 'HEAD', '--', ...self::RESET_DIRS])->getOutput();
        $untracked = $this->git(['ls-files', '--others', '--exclude-standard', '--directory', '--', ...self::RESET_DIRS])->getOutput();

        return [
            'modified' => array_values(array_filter(explode("\n", $modified))),
            'untracked' => array_values(array_unique(array_filter([...explode("\n", $added), ...explode("\n", $untracked)]))),
        ];
    }

    /**
     * Discard ALL local changes, not only the ones made by patches.
     * Modified tracked files are restored and untracked files in the code directories are deleted.
     *
     * @throws PatchException
     */
    public function reset(): void
    {
        $this->ensureRepository();

        $this->git(['reset', '--quiet']);
        $this->git(['checkout', '--', '.']);
        $this->git(['clean', '-d', '--force', '--quiet', '--', ...self::RESET_DIRS]);

        foreach (array_keys($this->all()) as $pr) {
            $this->forget($pr);
        }
    }

    /**
     * Find the commit that merged the pull request, if it is part of $ref
     */
    public function mergeCommit(Patch $patch, string $ref): ?string
    {
        $info = $this->pullRequestInfo($patch->pr);

        if ($info !== null) {
            $commit = $info['merge_commit_sha'] ?? null;

            return ! empty($info['merged']) && is_string($commit) && $this->git(['merge-base', '--is-ancestor', $commit, $ref])->isSuccessful()
                ? $commit
                : null;
        }

        // GitHub is not reachable, look for the squash merge commit subject
        $suffix = "(#$patch->pr)";
        $log = $this->git(['log', '--format=%H %s', '--fixed-strings', "--grep=$suffix", $ref]);

        foreach (explode("\n", $log->getOutput()) as $line) {
            if (str_ends_with(trim($line), $suffix)) {
                return strtok($line, ' ') ?: null;
            }
        }

        return null;
    }

    /**
     * Fetch pull request details from the GitHub API
     *
     * @return array<string, mixed>|null null if GitHub could not be reached
     */
    public function pullRequestInfo(int $pr): ?array
    {
        try {
            $response = Http::client()->timeout(15)->get($this->apiUrl . "pulls/$pr");

            if ($response->successful()) {
                return (array) $response->json();
            }

            Log::debug("GitHub API returned {$response->status()} for pull request #$pr");
        } catch (\Exception $e) {
            Log::debug("Could not reach GitHub API: {$e->getMessage()}");
        }

        return null;
    }

    /**
     * Reverse the patch in the working tree. If that conflicts with patches applied on top of it,
     * unwind those first and then re-apply them.
     * Everything is tried on a copy first, so the working tree is only changed if the patch can be removed.
     *
     * Patches that can not be re-applied without this one are suspended.
     *
     * @return list<string> warnings
     *
     * @throws PatchException
     */
    private function unapply(Patch $patch): array
    {
        if ($patch->state !== Patch::APPLIED || $this->canApply($patch)) {
            return []; // not in the working tree, or the changes were already reverted outside of lnms
        }

        $later = $this->appliedAfter($patch);
        $unwind = $this->unwindSteps($patch, $later);
        $error = $this->simulate($unwind);
        if ($error !== null) {
            throw new PatchException("Changes from #$patch->pr have been modified and can not be removed cleanly:\n$error");
        }

        foreach ($unwind as [$step]) {
            $result = $this->gitApply($step, reverse: true);
            if (! $result->isSuccessful()) {
                throw new PatchException("Could not reverse #$step->pr:\n" . trim($result->getErrorOutput()));
            }
        }

        // put back what was unwound
        $warnings = [];
        foreach ($later as $other) {
            if (! $this->gitApply($other)->isSuccessful()) {
                $other->state = Patch::SUSPENDED;
                $this->save($other);
                $warnings[] = "#$other->pr depends on #$patch->pr and could not be re-applied. Remove it with lnms patch:remove $other->pr or apply its latest version with lnms patch:apply $other->pr";
            }
        }

        return $warnings;
    }

    /**
     * Applied patches that were applied after this one and change the same files (directly or through each other)
     *
     * @return list<Patch> in the order they were applied
     */
    private function appliedAfter(Patch $patch): array
    {
        $paths = $patch->paths();
        $later = [];
        $found = false;

        foreach ($this->all() as $pr => $other) {
            if ($pr === $patch->pr) {
                $found = true;
            } elseif ($found && $other->state === Patch::APPLIED && array_intersect($paths, $other->paths())) {
                $later[] = $other;
                $paths = array_merge($paths, $other->paths());
            }
        }

        return $later;
    }

    /**
     * Steps to reverse the patch: the later patches newest first, then the patch itself
     *
     * @param  list<Patch>  $later
     * @return list<array{Patch, bool}>
     */
    private function unwindSteps(Patch $patch, array $later): array
    {
        $steps = array_map(fn (Patch $other) => [$other, true], array_reverse($later));
        $steps[] = [$patch, true];

        return $steps;
    }

    /**
     * Run git apply steps on a copy of the files they change, the working tree is not touched
     *
     * @param  list<array{Patch, bool}>  $steps  patch and if it is reversed
     * @return string|null the error of the first step that failed
     */
    private function simulate(array $steps): ?string
    {
        $scratch = $this->tempDir();

        try {
            $paths = array_unique(array_merge(...array_map(fn (array $step) => $step[0]->paths(), $steps)));
            foreach ($paths as $path) {
                if (is_file("$this->baseDir/$path")) {
                    File::ensureDirectoryExists(dirname("$scratch/$path"));
                    copy("$this->baseDir/$path", "$scratch/$path");
                }
            }

            foreach ($steps as [$patch, $reverse]) {
                $result = $this->gitApply($patch, reverse: $reverse, dir: $scratch);
                if (! $result->isSuccessful()) {
                    return trim($result->getErrorOutput());
                }
            }

            return null;
        } finally {
            File::deleteDirectory($scratch);
        }
    }

    /**
     * @throws PatchException
     */
    private function ensureRepository(): void
    {
        if (! $this->git(['rev-parse', '--is-inside-work-tree'])->isSuccessful()) {
            throw new PatchException('This install is not a git repository, local changes can not be discarded');
        }
    }

    private function canApply(Patch $patch): bool
    {
        return $this->gitApply($patch, check: true)->isSuccessful();
    }

    private function save(Patch $patch): void
    {
        $this->all();
        $this->patches[$patch->pr] = $patch;

        if (! is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0775, true);
        }

        file_put_contents($this->diffFile($patch->pr), $patch->diff);
        $this->writeIndex();
    }

    private function forget(int $pr): void
    {
        $this->all();
        unset($this->patches[$pr]);

        if (is_file($this->diffFile($pr))) {
            unlink($this->diffFile($pr));
        }

        $this->writeIndex();
    }

    private function writeIndex(): void
    {
        $index = $this->storageDir . '/patches.json';

        if (empty($this->patches)) {
            if (is_file($index)) {
                unlink($index);
            }

            return;
        }

        $records = array_values(array_map(fn (Patch $patch) => $patch->toArray(), $this->patches));
        file_put_contents($index, json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    }

    private function diffFile(int $pr): string
    {
        return "$this->storageDir/$pr.diff";
    }

    /**
     * @return PatchFile
     */
    private function parseSection(string $section): array
    {
        if (preg_match('/^rename from (.+)$/m', $section, $from) && preg_match('/^rename to (.+)$/m', $section, $to)) {
            return ['status' => 'R', 'path' => $to[1], 'old' => $from[1]];
        }

        // header is "diff --git a/<path> b/<path>", the path is the same on both sides
        $header = substr(strtok($section, "\n") ?: '', strlen('diff --git '));
        $path = substr($header, 2, intdiv(strlen($header) - 5, 2));

        if (preg_match('/^new file mode/m', $section)) {
            $status = 'A';
        } elseif (preg_match('/^deleted file mode/m', $section)) {
            $status = 'D';
        } else {
            $status = 'M';
        }

        return ['status' => $status, 'path' => $path];
    }

    /**
     * Replace a "Binary files differ" section with a real binary patch from the local file to the new one
     *
     * @param  PatchFile  $file
     *
     * @throws PatchException
     */
    private function binarySection(string $section, array $file, string $ref): string
    {
        $tmp = $this->tempDir();
        $old = '/dev/null';
        $new = '/dev/null';

        try {
            if ($file['status'] !== 'A') {
                $path = $file['old'] ?? $file['path'];
                if (! is_file("$this->baseDir/$path")) {
                    throw new PatchException("$path does not exist");
                }
                $old = "a/$path";
                File::ensureDirectoryExists(dirname("$tmp/$old"));
                copy("$this->baseDir/$path", "$tmp/$old");
            }

            if ($file['status'] !== 'D') {
                $new = "b/{$file['path']}";
                $url = sprintf(self::RAW_URL, $ref, implode('/', array_map(rawurlencode(...), explode('/', $file['path']))));
                File::ensureDirectoryExists(dirname("$tmp/$new"));
                file_put_contents("$tmp/$new", $this->download($url, $file['path']));
            }

            // exits 1 when the files differ
            $process = $this->git(['diff', '--no-index', '--binary', '--full-index', '--no-prefix', '--no-color', '--no-ext-diff', $old, $new], dir: $tmp);
            if ($process->getExitCode() > 1) {
                throw new PatchException("Could not create binary patch for {$file['path']}:\n" . trim($process->getErrorOutput()));
            }
            $generated = $process->getOutput();
        } finally {
            File::deleteDirectory($tmp);
        }

        // keep the original header (file modes, renames) and use the generated index and binary data
        $header = strstr($section, "\nindex ", true) ?: strstr($section, "\nBinary files ", true);
        $content = strstr($generated, "\nindex ");

        return $content === false
            ? ($file['status'] === 'R' ? "$header\n" : '') // no content changes
            : $header . $content;
    }

    /**
     * @throws PatchException
     */
    private function download(string $url, string $description): string
    {
        try {
            $response = Http::client()->timeout(120)->get($url);
        } catch (\Exception $e) {
            throw new PatchException("Could not download $description: {$e->getMessage()}");
        }

        if ($response->status() === 404) {
            throw new PatchException(ucfirst($description) . ' not found on GitHub');
        }

        if (! $response->successful()) {
            throw new PatchException("Could not download $description: HTTP {$response->status()}");
        }

        return $response->body();
    }

    private function tempDir(): string
    {
        $dir = sys_get_temp_dir() . '/lnms-patch-' . bin2hex(random_bytes(6));
        File::ensureDirectoryExists($dir);

        return $dir;
    }

    /**
     * @param  string|null  $dir  directory to apply in, defaults to the working tree
     */
    private function gitApply(Patch $patch, bool $reverse = false, bool $check = false, ?string $dir = null): Process
    {
        $args = ['apply', '--whitespace=nowarn'];
        if ($reverse) {
            $args[] = '--reverse';
        }
        if ($check) {
            $args[] = '--check';
        }

        return $this->git([...$args, '-'], $patch->diff, $dir);
    }

    /**
     * @param  list<string>  $args
     */
    private function git(array $args, ?string $input = null, ?string $dir = null): Process
    {
        $dir ??= $this->baseDir;

        // GIT_CEILING_DIRECTORIES: installs without .git (docker) must not pick up a repository in a parent directory
        $process = new Process(['git', ...$args], $dir, ['GIT_TERMINAL_PROMPT' => '0', 'GIT_CEILING_DIRECTORIES' => dirname($dir)]);
        $process->setInput($input);
        $process->run();

        Log::debug('git ' . implode(' ', $args) . ' exited ' . $process->getExitCode());

        return $process;
    }
}
