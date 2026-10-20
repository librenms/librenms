<?php

/**
 * RancidProvider.php
 *
 * Config backup provider backed by a RANCID repository (git, git-bare or svn)
 * on the local filesystem. CVS repositories only expose the current config.
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
 * @copyright  2026 LibreNMS
 */

namespace App\ConfigBackup\Providers;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Eventlog;
use Illuminate\Support\Facades\Cache;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\ConfigBackupProvider;
use Symfony\Component\Process\Process;

class RancidProvider implements ConfigBackupProvider
{
    /** Revisions listed per page (matches OxidizedProvider). */
    private const PER_PAGE = 100;

    /** Backup id for the on-disk file when there is no VCS history. */
    private const CURRENT = 'current';

    /** Unchanged lines kept around each change when diffs are rebuilt without comments. */
    private const DIFF_CONTEXT = 3;

    /** Seconds between repeated eventlog entries for the same unreadable repo. */
    private const FAILURE_LOG_INTERVAL = 3600;

    private ?string $lastError = null;

    private string $lastCommandError = '';

    /** @var array<int, array{path: string, file: string}|null> */
    private array $resolved = [];

    public static function isConfigured(): bool
    {
        return ! empty(LibrenmsConfig::get('rancid_configs'));
    }

    public function supportsDevice(Device $device): bool
    {
        return $this->resolve($device) !== null;
    }

    public function name(): string
    {
        return 'RANCID';
    }

    public function backups(Device $device, int $page = 0): ?array
    {
        $this->lastError = null;

        $repo = $this->resolve($device);
        if ($repo === null) {
            $this->lastError = self::ERROR_DEVICE_NOT_FOUND;

            return null;
        }

        $isGit = in_array(LibrenmsConfig::get('rancid_repo_type'), ['git', 'git-bare'], true);
        // git can count and page server side; svn/cvs need the whole log anyway
        $all = $isGit ? null : $this->history($device, $repo);
        $total = $isGit ? $this->countRevisions($device, $repo) : ($all === null ? null : count($all));

        if (! $total) {
            // No usable history (e.g. CVS, or file not committed yet): offer the current file alone
            $current = $this->currentFile($repo);
            if ($current === null) {
                $this->lastError = $total === null ? self::ERROR_UNREACHABLE : self::ERROR_NO_BACKUPS;

                return null;
            }

            return [
                'backups' => [$this->entry(self::CURRENT, $current['date'])],
                'total' => 1,
                'totalPages' => 1,
                'page' => 0,
            ];
        }

        $totalPages = (int) ceil($total / self::PER_PAGE);
        $page = max(0, min($page, $totalPages - 1));
        $backups = $all === null
            ? $this->history($device, $repo, self::PER_PAGE, $page * self::PER_PAGE)
            : array_slice($all, $page * self::PER_PAGE, self::PER_PAGE);
        if ($backups === null) {
            $this->lastError = self::ERROR_UNREACHABLE;

            return null;
        }

        return [
            'backups' => $backups,
            'total' => $total,
            'totalPages' => $totalPages,
            'page' => $page,
        ];
    }

    public function latest(Device $device): ?array
    {
        $this->lastError = null;

        $repo = $this->resolve($device);
        if ($repo === null) {
            $this->lastError = self::ERROR_DEVICE_NOT_FOUND;

            return null;
        }

        $history = $this->history($device, $repo, 1);
        if (! empty($history)) {
            $latest = $history[0];
            $latest['content'] = $this->content($device, $latest['id']);

            return $latest['content'] === null ? null : $latest;
        }

        $current = $this->currentFile($repo);
        if ($current === null) {
            $this->lastError = $history === null ? self::ERROR_UNREACHABLE : self::ERROR_NO_BACKUPS;

            return null;
        }

        $latest = $this->entry(self::CURRENT, $current['date']);
        $latest['content'] = $this->filterComments($current['content']);

        return $latest;
    }

    public function content(Device $device, string $backupId, int $pageHint = 0): ?string
    {
        $this->lastError = null;

        $repo = $this->resolve($device);
        if ($repo === null) {
            $this->lastError = self::ERROR_DEVICE_NOT_FOUND;

            return null;
        }

        $type = LibrenmsConfig::get('rancid_repo_type');
        $content = null;

        if ($backupId === self::CURRENT) {
            $content = $this->currentFile($repo)['content'] ?? null;
        } elseif ($type === 'svn' && ctype_digit($backupId)) {
            $content = $this->svn(['cat', '-r', $backupId, $repo['file']], $repo);
        } elseif (($type === 'git' || $type === 'git-bare') && ctype_xdigit($backupId)) {
            // rev:path is relative to the repo root; "./" makes it relative to the configs dir (bare repos have no cwd-relative form)
            $file = $type === 'git' ? './' . $repo['file'] : $repo['file'];
            $content = $this->git(['show', $backupId . ':' . $file], $repo);
        }

        if ($content === null) {
            $this->lastError = self::ERROR_BACKUP_NOT_FOUND;

            return null;
        }

        return $this->filterComments($content);
    }

    public function diff(Device $device, string $origId, string $revId): ?array
    {
        $this->lastError = null;

        $repo = $this->resolve($device);
        if ($repo === null) {
            $this->lastError = self::ERROR_DEVICE_NOT_FOUND;

            return null;
        }

        $type = LibrenmsConfig::get('rancid_repo_type');
        $ignoreComments = (bool) LibrenmsConfig::get('rancid_ignorecomments');
        // Without comments, diff the whole file so line numbers can be recounted to match the filtered content
        $context = $ignoreComments ? ['-U999999'] : [];

        if ($type === 'svn') {
            if (! ctype_digit($origId) || ! ctype_digit($revId)) {
                $this->lastError = self::ERROR_BACKUP_NOT_FOUND;

                return null;
            }
            $extensions = $ignoreComments ? ['-x', '-U999999'] : [];
            $diff = $this->svn(['diff', ...$extensions, '-r', $origId . ':' . $revId, $repo['file']], $repo);
        } elseif ($type === 'git' || $type === 'git-bare') {
            if (! ctype_xdigit($origId) || ! ctype_xdigit($revId)) {
                $this->lastError = self::ERROR_BACKUP_NOT_FOUND;

                return null;
            }
            $diff = $this->git(['diff', ...$context, $origId, $revId, '--', $repo['file']], $repo);
        } else {
            $this->lastError = self::ERROR_BACKUP_NOT_FOUND; // no history to diff without svn/git

            return null;
        }

        if ($diff === null) {
            // If the history is readable the repo is fine, so one of the revisions is bad
            $this->lastError = $this->history($device, $repo, 1) === null ? self::ERROR_UNREACHABLE : self::ERROR_BACKUP_NOT_FOUND;

            return null;
        }

        if ($ignoreComments) {
            return $this->trimContext($this->parseUnifiedDiff($diff, true));
        }

        return $this->parseUnifiedDiff($diff);
    }

    public function lastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Number of git revisions of the device config, or null when the repo cannot be read.
     *
     * @param  array{path: string, file: string}  $repo
     */
    private function countRevisions(Device $device, array $repo): ?int
    {
        $count = $this->git(['rev-list', '--count', 'HEAD', '--', $repo['file']], $repo);

        return $count === null ? $this->logFailure($device, $repo) : (int) trim($count);
    }

    /**
     * Revisions of the device config, newest first.
     * Null when the VCS cannot be read or rancid_repo_type has no history support.
     *
     * @param  array{path: string, file: string}  $repo
     * @return list<array{id: string, date: ?int, until: ?int, type: string, content: ?string}>|null
     */
    private function history(Device $device, array $repo, ?int $limit = null, int $skip = 0): ?array
    {
        $type = LibrenmsConfig::get('rancid_repo_type');
        $entries = [];

        if ($type === 'svn') {
            // svn log cannot skip, so fetch up to the end of the wanted page and slice
            $limitArgs = $limit === null ? [] : ['-l', (string) ($skip + $limit)];
            $xml = $this->svn(['log', '-q', '--xml', ...$limitArgs, $repo['file']], $repo);
            if ($xml === null) {
                return $this->logFailure($device, $repo);
            }

            $parsed = @simplexml_load_string($xml);
            if ($parsed !== false) {
                foreach ($parsed->logentry as $entry) {
                    $entries[] = $this->entry((string) $entry['revision'], strtotime((string) $entry->date) ?: null);
                }
            }

            return array_slice($entries, $skip, $limit);
        }

        if ($type === 'git' || $type === 'git-bare') {
            $limitArgs = $limit === null ? [] : ['-n', (string) $limit];
            $log = $this->git(['log', '--pretty=format:%H %ct', ...$limitArgs, '--skip', (string) $skip, '--', $repo['file']], $repo);
            if ($log === null) {
                return $this->logFailure($device, $repo);
            }

            foreach (explode("\n", trim($log)) as $line) {
                if ($line === '') {
                    continue;
                }
                [$hash, $ts] = array_pad(explode(' ', $line, 2), 2, '');
                $entries[] = $this->entry($hash, (int) $ts);
            }

            return $entries;
        }

        return null; // cvs: no history support, only the current file
    }

    /**
     * Record in the device eventlog why the repository could not be read, so a broken
     * setup (e.g. git refusing a repo owned by the rancid user) does not silently degrade
     * to showing only the current file. Logged at most once per FAILURE_LOG_INTERVAL.
     *
     * @param  array{path: string, file: string}  $repo
     */
    private function logFailure(Device $device, array $repo): null
    {
        $message = sprintf(
            'RANCID: could not read %s history of %s in %s: %s',
            LibrenmsConfig::get('rancid_repo_type'),
            $repo['file'],
            $repo['path'],
            preg_replace('/\s+/', ' ', trim($this->lastCommandError)),
        );

        if (Cache::add('rancid-failure:' . $device->device_id . ':' . md5($message), true, self::FAILURE_LOG_INTERVAL)) {
            Eventlog::log($message, $device, 'config', Severity::Warning);
        }

        return null;
    }

    /**
     * The config file as it is on disk (not available for bare repos).
     *
     * @param  array{path: string, file: string}  $repo
     * @return array{content: string, date: ?int}|null
     */
    private function currentFile(array $repo): ?array
    {
        if (LibrenmsConfig::get('rancid_repo_type') === 'git-bare') {
            return null;
        }

        $path = $repo['path'] . '/' . $repo['file'];
        $content = is_readable($path) ? file_get_contents($path) : false;
        if ($content === false) {
            return null;
        }

        return ['content' => $content, 'date' => filemtime($path) ?: null];
    }

    private function isComment(string $line): bool
    {
        return LibrenmsConfig::get('rancid_ignorecomments') && str_starts_with($line, '#');
    }

    private function filterComments(string $content): string
    {
        if (! LibrenmsConfig::get('rancid_ignorecomments')) {
            return $content;
        }

        return implode("\n", array_filter(explode("\n", $content), fn ($line) => ! $this->isComment($line)));
    }

    /**
     * @return array{id: string, date: ?int, until: ?int, type: string, content: ?string}
     */
    private function entry(string $id, ?int $date): array
    {
        return ['id' => $id, 'date' => $date, 'until' => null, 'type' => 'TEXT', 'content' => null];
    }

    /**
     * Locate the repository directory and the device's config file within it.
     *
     * @return array{path: string, file: string}|null
     */
    private function resolve(Device $device): ?array
    {
        $deviceId = $device->device_id;
        if (array_key_exists($deviceId, $this->resolved)) {
            return $this->resolved[$deviceId];
        }

        $this->resolved[$deviceId] = null;
        $configs = (array) LibrenmsConfig::get('rancid_configs', []);
        $names = $this->nameCandidates($device);

        if (LibrenmsConfig::get('rancid_repo_type') === 'git-bare') {
            foreach ($configs as $configured) {
                $bare = $this->findBareRepo($configured);
                if ($bare === null) {
                    continue;
                }

                $repo = ['path' => $bare['repo'], 'file' => ''];
                $paths = array_map(fn ($name) => $bare['prefix'] . $name, $names);
                $tree = $this->git(['ls-tree', '--name-only', 'HEAD', '--', ...$paths], $repo);
                if ($tree === null) {
                    $this->logFailure($device, $repo);
                    continue;
                }

                $found = explode("\n", $tree);
                foreach ($paths as $path) {
                    if (in_array($path, $found, true)) {
                        return $this->resolved[$deviceId] = ['path' => $bare['repo'], 'file' => $path];
                    }
                }
            }

            return null;
        }

        foreach ($configs as $dir) {
            $dir = rtrim($dir, '/');
            foreach ($names as $name) {
                if (is_file($dir . '/' . $name)) {
                    return $this->resolved[$deviceId] = ['path' => $dir, 'file' => $name];
                }
            }
        }

        return null;
    }

    /**
     * Split a configured git-bare path such as /var/lib/rancid/CVS/core/configs or
     * /some/path/rancid.git/configs into the bare repository and the path inside it.
     * A path without a matching directory falls back to a "<path>.git" sibling.
     *
     * @return array{repo: string, prefix: string}|null
     */
    private function findBareRepo(string $configured): ?array
    {
        $configured = rtrim($configured, '/');

        for ($dir = $configured; $dir !== '' && $dir !== '/' && $dir !== '.'; $dir = dirname($dir)) {
            if (is_file("$dir/HEAD") && is_dir("$dir/objects") && is_dir("$dir/refs")) {
                $prefix = ltrim(substr($configured, strlen($dir)), '/');

                return ['repo' => $dir, 'prefix' => $prefix === '' ? '' : $prefix . '/'];
            }
        }

        if (is_dir($configured . '.git')) {
            return ['repo' => $configured . '.git', 'prefix' => ''];
        }

        return null;
    }

    /**
     * Config file name variants to look for.
     *
     * @return list<string>
     */
    private function nameCandidates(Device $device): array
    {
        $candidates = [
            $device->hostname,
            strtok($device->hostname, '.'),
        ];

        $domain = LibrenmsConfig::get('mydomain');
        if (! empty($domain)) {
            $candidates[] = $device->hostname . '.' . $domain;
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    /**
     * @param  list<string>  $args
     * @param  array{path: string, file: string}  $repo
     */
    private function git(array $args, array $repo): ?string
    {
        return $this->run(['git', '--literal-pathspecs', ...$args], $repo['path']);
    }

    /**
     * @param  list<string>  $args
     * @param  array{path: string, file: string}  $repo
     */
    private function svn(array $args, array $repo): ?string
    {
        return $this->run([LibrenmsConfig::locateBinary('svn'), '--non-interactive', ...$args], $repo['path']);
    }

    /**
     * Run a command in a directory, returning its stdout or null on failure.
     *
     * @param  list<string>  $command
     */
    private function run(array $command, string $cwd): ?string
    {
        $process = new Process($command, $cwd);
        $process->run();
        $this->lastCommandError = $process->getErrorOutput();

        return $process->isSuccessful() ? $process->getOutput() : null;
    }

    /**
     * Convert a unified diff into the structured line groups the Config tab
     * renders. Lines before the first hunk are ignored; each remaining line
     * becomes its own group.
     *
     * With $skipComments, comment lines are dropped and line numbers count only
     * the kept lines. That requires a full-context diff starting at line 1.
     *
     * @return list<array{type: string, original: list<array{line: ?int, text: string}>, revised: list<array{line: ?int, text: string}>}>
     */
    private function parseUnifiedDiff(string $diff, bool $skipComments = false): array
    {
        $groups = [];
        $origLine = 0;
        $revLine = 0;
        $inHunk = false;

        foreach (explode("\n", $diff) as $line) {
            if (str_starts_with($line, '@@')) {
                if ($skipComments) {
                    $origLine = $revLine = 1;
                } elseif (preg_match('/@@ -(\d+)(?:,\d+)? \+(\d+)(?:,\d+)? @@/', $line, $m)) {
                    $origLine = (int) $m[1];
                    $revLine = (int) $m[2];
                }
                $inHunk = true;

                continue;
            }

            if (! $inHunk || $line === '') {
                continue;
            }

            $text = substr($line, 1);
            if ($skipComments && $this->isComment($text)) {
                continue;
            }

            switch ($line[0]) {
                case ' ':
                    $groups[] = ['type' => 'COMMON', 'original' => [['line' => $origLine, 'text' => $text]], 'revised' => [['line' => $revLine, 'text' => $text]]];
                    $origLine++;
                    $revLine++;
                    break;
                case '-':
                    $groups[] = ['type' => 'DELETED', 'original' => [['line' => $origLine, 'text' => $text]], 'revised' => []];
                    $origLine++;
                    break;
                case '+':
                    $groups[] = ['type' => 'INSERTED', 'original' => [], 'revised' => [['line' => $revLine, 'text' => $text]]];
                    $revLine++;
                    break;
                default:
                    // "\ No newline at end of file" and any stray markers
                    break;
            }
        }

        return $groups;
    }

    /**
     * Drop unchanged lines further than DIFF_CONTEXT lines from any change,
     * like a normal unified diff. No changes leaves no groups.
     *
     * @param  list<array{type: string, original: list<array{line: ?int, text: string}>, revised: list<array{line: ?int, text: string}>}>  $groups
     * @return list<array{type: string, original: list<array{line: ?int, text: string}>, revised: list<array{line: ?int, text: string}>}>
     */
    private function trimContext(array $groups): array
    {
        $keep = [];
        foreach ($groups as $index => $group) {
            if ($group['type'] !== 'COMMON') {
                for ($i = $index - self::DIFF_CONTEXT; $i <= $index + self::DIFF_CONTEXT; $i++) {
                    $keep[$i] = true;
                }
            }
        }

        return array_values(array_intersect_key($groups, $keep));
    }
}
