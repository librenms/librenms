<?php

/**
 * RancidProvider.php
 *
 * Config backup provider backed by a RANCID repository (git, git-bare or svn)
 * on the local filesystem.
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
use LibreNMS\Interfaces\ConfigBackupProvider;
use Symfony\Component\Process\Process;

class RancidProvider implements ConfigBackupProvider
{
    /** Revisions listed per page (matches OxidizedProvider). */
    private const PER_PAGE = 100;

    private const CURRENT = 'current';

    private ?string $lastError = null;

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

        $all = $this->history($repo);
        if (empty($all)) {
            // No usable history (e.g. CVS, or file not committed yet): offer the current file alone
            $current = $this->currentFile($repo);
            if ($current === null) {
                $this->lastError = $all === null ? self::ERROR_UNREACHABLE : self::ERROR_NO_BACKUPS;

                return null;
            }
            $all = [$this->entry(self::CURRENT, $current['date'])];
        }

        // The VCS returns the full history; page it like OxidizedProvider so the
        // UI does not choke on devices with thousands of revisions.
        $total = count($all);
        $totalPages = (int) ceil($total / self::PER_PAGE);
        $page = max(0, min($page, $totalPages - 1));

        return [
            'backups' => array_slice($all, $page * self::PER_PAGE, self::PER_PAGE),
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

        $history = $this->history($repo, 1);
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
            $svn = LibrenmsConfig::locateBinary('svn');
            $content = $this->run([$svn, 'cat', '-r', $backupId, $repo['file']], $repo['path']);
        } elseif (($type === 'git' || $type === 'git-bare') && ctype_xdigit($backupId)) {
            // rev:path is relative to the repo root; "./" makes it relative to the configs dir (bare repos have no cwd-relative form)
            $file = $type === 'git' ? './' . $repo['file'] : $repo['file'];
            $content = $this->run(['git', 'show', $backupId . ':' . $file], $repo['path']);
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

        if ($type === 'svn') {
            if (! ctype_digit($origId) || ! ctype_digit($revId)) {
                $this->lastError = self::ERROR_BACKUP_NOT_FOUND;

                return null;
            }
            $svn = LibrenmsConfig::locateBinary('svn');
            $diff = $this->run([$svn, 'diff', '-r', $origId . ':' . $revId, $repo['file']], $repo['path']);
        } elseif ($type === 'git' || $type === 'git-bare') {
            if (! ctype_xdigit($origId) || ! ctype_xdigit($revId)) {
                $this->lastError = self::ERROR_BACKUP_NOT_FOUND;

                return null;
            }
            $diff = $this->run(['git', 'diff', $origId, $revId, '--', $repo['file']], $repo['path']);
        } else {
            $this->lastError = self::ERROR_BACKUP_NOT_FOUND; // no history to diff without svn/git

            return null;
        }

        if ($diff === null) {
            // If the history is readable the repo is fine, so one of the revisions is bad
            $this->lastError = $this->history($repo, 1) === null ? self::ERROR_UNREACHABLE : self::ERROR_BACKUP_NOT_FOUND;

            return null;
        }

        return $this->parseUnifiedDiff($diff);
    }

    public function lastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Revisions of the device config, newest first.
     * Null when the VCS cannot be read or rancid_repo_type has no history support.
     *
     * @param  array{path: string, file: string}  $repo
     * @return list<array{id: string, date: ?int, until: ?int, type: string, content: ?string}>|null
     */
    private function history(array $repo, ?int $limit = null): ?array
    {
        $type = LibrenmsConfig::get('rancid_repo_type');
        $entries = [];

        if ($type === 'svn') {
            $svn = LibrenmsConfig::locateBinary('svn');
            $limitArgs = $limit === null ? [] : ['-l', (string) $limit];
            $xml = $this->run([$svn, 'log', '-q', '--xml', ...$limitArgs, $repo['file']], $repo['path']);
            if ($xml === null) {
                return null;
            }

            $parsed = @simplexml_load_string($xml);
            if ($parsed !== false) {
                foreach ($parsed->logentry as $entry) {
                    $entries[] = $this->entry((string) $entry['revision'], strtotime((string) $entry->date) ?: null);
                }
            }

            return $entries;
        }

        if ($type === 'git' || $type === 'git-bare') {
            $limitArgs = $limit === null ? [] : ['-n', (string) $limit];
            $log = $this->run(['git', 'log', '--pretty=format:%H %ct', ...$limitArgs, '--', $repo['file']], $repo['path']);
            if ($log === null) {
                return null;
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

    private function filterComments(string $content): string
    {
        if (! LibrenmsConfig::get('rancid_ignorecomments')) {
            return $content;
        }

        return implode("\n", array_filter(
            explode("\n", $content),
            fn ($line) => ! str_starts_with($line, '#'),
        ));
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
            foreach ($configs as $repo) {
                $dotGit = strpos($repo, '.git');
                $prefix = '';
                if ($dotGit === false) {
                    if (! is_dir($repo . '.git')) {
                        continue;
                    }
                    $repo .= '.git';
                } else {
                    $prefix = substr($repo, $dotGit + 5);
                    $repo = substr($repo, 0, $dotGit + 4);
                    if ($prefix !== '' && ! str_ends_with($prefix, '/')) {
                        $prefix .= '/';
                    }
                }

                $tree = $this->run(['git', 'ls-tree', '--name-only', '-r', 'HEAD'], $repo);
                if ($tree === null) {
                    continue;
                }

                $files = explode("\n", $tree);
                foreach ($names as $name) {
                    if (in_array($prefix . $name, $files, true)) {
                        return $this->resolved[$deviceId] = ['path' => $repo, 'file' => $prefix . $name];
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
     * Run a command in a directory, returning its stdout or null on failure.
     *
     * @param  list<string>  $command
     */
    private function run(array $command, string $cwd): ?string
    {
        $process = new Process($command, $cwd);
        $process->run();

        return $process->isSuccessful() ? $process->getOutput() : null;
    }

    /**
     * Convert a unified diff into the structured line groups the Config tab
     * renders. Lines before the first hunk are ignored; each remaining line
     * becomes its own group.
     *
     * @return list<array{type: string, original: list<array{line: ?int, text: string}>, revised: list<array{line: ?int, text: string}>}>
     */
    private function parseUnifiedDiff(string $diff): array
    {
        $groups = [];
        $origLine = 0;
        $revLine = 0;
        $inHunk = false;

        foreach (explode("\n", $diff) as $line) {
            if (str_starts_with($line, '@@')) {
                if (preg_match('/@@ -(\d+)(?:,\d+)? \+(\d+)(?:,\d+)? @@/', $line, $m)) {
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
}
