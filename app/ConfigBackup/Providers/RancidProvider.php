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

        $type = LibrenmsConfig::get('rancid_repo_type');
        $all = [];

        if ($type === 'svn') {
            $svn = LibrenmsConfig::locateBinary('svn');
            $xml = $this->run([$svn, 'log', '-q', '--xml', $repo['file']], $repo['path']);
            if ($xml === null) {
                $this->lastError = self::ERROR_UNREACHABLE;

                return null;
            }

            $parsed = @simplexml_load_string($xml);
            if ($parsed !== false) {
                foreach ($parsed->logentry as $entry) {
                    $all[] = $this->entry((string) $entry['revision'], strtotime((string) $entry->date) ?: null);
                }
            }
        } elseif ($type === 'git' || $type === 'git-bare') {
            $log = $this->run(['git', 'log', '--pretty=format:%H %ct', '--', $repo['file']], $repo['path']);
            if ($log === null) {
                $this->lastError = self::ERROR_UNREACHABLE;

                return null;
            }

            foreach (explode("\n", trim($log)) as $line) {
                if ($line === '') {
                    continue;
                }
                [$hash, $ts] = array_pad(explode(' ', $line, 2), 2, '');
                $all[] = $this->entry($hash, (int) $ts);
            }
        } else {
            $this->lastError = self::ERROR_UNREACHABLE; // unsupported rancid_repo_type

            return null;
        }

        // The VCS returns the full history; page it like OxidizedProvider so the
        // UI does not choke on devices with thousands of revisions.
        $total = count($all);
        $totalPages = $total === 0 ? 1 : (int) ceil($total / self::PER_PAGE);
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
        $list = $this->backups($device);
        if ($list === null || $list['backups'] === []) {
            return null;
        }

        $latest = $list['backups'][0];
        $latest['content'] = $this->content($device, $latest['id']);
        $this->lastError = null;

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

        if ($type === 'svn') {
            if (! ctype_digit($backupId)) {
                $this->lastError = self::ERROR_BACKUP_NOT_FOUND;

                return null;
            }
            $svn = LibrenmsConfig::locateBinary('svn');
            $content = $this->run([$svn, 'cat', '-r', $backupId, $repo['file']], $repo['path']);
        } elseif ($type === 'git' || $type === 'git-bare') {
            if (! ctype_xdigit($backupId)) {
                $this->lastError = self::ERROR_BACKUP_NOT_FOUND;

                return null;
            }
            $content = $this->run(['git', 'show', $backupId . ':' . $repo['file']], $repo['path']);
        } else {
            $this->lastError = self::ERROR_BACKUP_NOT_FOUND; // unsupported rancid_repo_type

            return null;
        }

        if ($content === null) {
            $this->lastError = self::ERROR_BACKUP_NOT_FOUND;

            return null;
        }

        if (LibrenmsConfig::get('rancid_ignorecomments')) {
            $content = implode("\n", array_filter(
                explode("\n", $content),
                fn ($line) => ! str_starts_with($line, '#'),
            ));
        }

        return $content;
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
            $this->lastError = self::ERROR_BACKUP_NOT_FOUND; // unsupported rancid_repo_type

            return null;
        }

        if ($diff === null) {
            $this->lastError = self::ERROR_UNREACHABLE;

            return null;
        }

        return $this->parseUnifiedDiff($diff);
    }

    public function lastError(): ?string
    {
        return $this->lastError;
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
