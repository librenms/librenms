<?php

/**
 * OxidizedProvider.php
 *
 * Config backup provider backed by the Oxidized REST API.
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

use App\ApiClients\Oxidized;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use LibreNMS\Interfaces\ConfigBackupProvider;

class OxidizedProvider implements ConfigBackupProvider
{
    /** Backup id used for the single current config when versioning is disabled. */
    private const CONFIG_ID = 'current';

    private ?string $lastError = null;

    /** @var array<int, array{node: ?array<string, mixed>, error: ?string}> */
    private array $nodes = [];

    /** @var array<int, array{list: ?list<array<string, mixed>>, error: ?string}> */
    private array $versionLists = [];

    public function __construct(
        private readonly Oxidized $api,
    ) {
    }

    public static function isConfigured(): bool
    {
        return Oxidized::isConfigured();
    }

    public function supportsDevice(Device $device): bool
    {
        return $device->getAttrib('override_Oxidized_disable') !== 'true'
            && ! in_array($device->type, LibrenmsConfig::get('oxidized.ignore_types', []) ?: [], true)
            && ! in_array($device->os, LibrenmsConfig::get('oxidized.ignore_os', []) ?: [], true);
    }

    public function name(): string
    {
        return 'Oxidized';
    }

    public function backups(Device $device, int $page = 0): ?array
    {
        $this->lastError = null;

        $node = $this->resolveNode($device);
        if ($node === null) {
            return null;
        }

        $versions = $this->versions($device, $node);
        if ($versions === null) {
            return null;
        }

        $backups = $this->mapVersions($versions);

        return [
            'backups' => $backups,
            'total' => count($backups),
            'totalPages' => 1,
            'page' => 0,
        ];
    }

    public function latest(Device $device): ?array
    {
        $this->lastError = null;

        $node = $this->resolveNode($device);
        if ($node === null) {
            return null;
        }

        $versions = $this->versions($device, $node);
        if ($versions === null) {
            return null;
        }

        $backups = $this->mapVersions($versions);
        if ($backups === []) {
            return null;
        }

        $latest = $backups[0];
        $latest['content'] = $this->fetchContent($node, $latest['id']);

        return $latest;
    }

    public function content(Device $device, string $backupId, int $pageHint = 0): ?string
    {
        $this->lastError = null;

        if ($backupId !== self::CONFIG_ID && ! ctype_xdigit($backupId)) {
            $this->lastError = self::ERROR_BACKUP_NOT_FOUND;

            return null;
        }

        $node = $this->resolveNode($device);
        if ($node === null) {
            return null;
        }

        $content = $this->fetchContent($node, $backupId);
        if ($content === null) {
            $this->lastError = $this->api->lastError() ?? self::ERROR_BACKUP_NOT_FOUND;

            return null;
        }

        return $content;
    }

    public function diff(Device $device, string $origId, string $revId): ?array
    {
        $this->lastError = null;

        if (! ctype_xdigit($origId) || ! ctype_xdigit($revId)) {
            $this->lastError = self::ERROR_BACKUP_NOT_FOUND;

            return null;
        }

        $node = $this->resolveNode($device);
        if ($node === null) {
            return null;
        }

        // Oxidized takes the newer version as oid and the older as oid2, so the
        // resulting diff removes the original (older) lines and inserts the new.
        $diff = $this->api->getDiff($node['name'], $revId, $origId);
        if ($diff === null) {
            $this->lastError = $this->api->lastError() ?? self::ERROR_UNREACHABLE;

            return null;
        }

        return $this->parseUnifiedDiff($diff);
    }

    public function lastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveNode(Device $device): ?array
    {
        $id = $device->device_id;

        if (! array_key_exists($id, $this->nodes)) {
            $node = $this->api->findNode($device);
            $this->nodes[$id] = [
                'node' => $node,
                'error' => $node === null ? ($this->api->lastError() ?? self::ERROR_DEVICE_NOT_FOUND) : null,
            ];
        }

        if ($this->nodes[$id]['node'] === null) {
            $this->lastError = $this->nodes[$id]['error'];
        }

        return $this->nodes[$id]['node'];
    }

    /**
     * @param  array<string, mixed>  $node
     * @return list<array<string, mixed>>|null
     */
    private function versions(Device $device, array $node): ?array
    {
        $id = $device->device_id;

        if (! array_key_exists($id, $this->versionLists)) {
            $versions = $this->api->getVersions($node['full_name'] ?? $node['name']);

            if ($versions === null) {
                // A transport failure is a real error; any other non-2xx means
                // versioning is disabled, so fall back to a single current config.
                $this->versionLists[$id] = $this->api->lastError() === 'unreachable'
                    ? ['list' => null, 'error' => self::ERROR_UNREACHABLE]
                    : ['list' => [], 'error' => null];
            } else {
                $this->versionLists[$id] = ['list' => $versions, 'error' => null];
            }
        }

        if ($this->versionLists[$id]['list'] === null) {
            $this->lastError = $this->versionLists[$id]['error'];
        }

        return $this->versionLists[$id]['list'];
    }

    /**
     * @param  list<array<string, mixed>>  $versions
     * @return list<array{id: string, date: ?int, until: ?int, type: string, content: ?string}>
     */
    private function mapVersions(array $versions): array
    {
        if ($versions === []) {
            return [[
                'id' => self::CONFIG_ID,
                'date' => null,
                'until' => null,
                'type' => 'TEXT',
                'content' => null,
            ]];
        }

        return array_map(fn ($version) => [
            'id' => (string) ($version['oid'] ?? ''),
            'date' => isset($version['date']) ? (strtotime((string) $version['date']) ?: null) : null,
            'until' => null,
            'type' => 'TEXT',
            'content' => null,
        ], $versions);
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function fetchContent(array $node, string $backupId): ?string
    {
        if ($backupId === self::CONFIG_ID) {
            return $this->api->getNodeConfig($node['name'], $node['group'] ?? null);
        }

        return $this->api->getVersionContent($node['name'], $backupId);
    }

    /**
     * Convert Oxidized's unified git diff into the structured line groups the
     * Config tab renders. Lines before the first hunk (the git header) are
     * ignored; each remaining line becomes its own group.
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
