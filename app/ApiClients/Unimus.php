<?php

/**
 * Unimus.php
 *
 * Client for the Unimus device configuration backup API.
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

namespace App\ApiClients;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Unimus extends BaseApi
{
    private const DEVICE_ID_CACHE_TTL = 3600;
    private const DEVICE_ID_MISS_TTL = 300;
    private const NOT_FOUND_SENTINEL = -1;

    private readonly bool $enabled;
    private readonly string $token;
    private ?string $lastError = null;

    public function __construct()
    {
        $this->timeout = 30;
        $url = rtrim(LibrenmsConfig::get('unimus.url') ?? '', '/');
        $version = LibrenmsConfig::get('unimus.api_version') ?: 'v2';
        $this->base_uri = $url ? "$url/api/$version" : '';
        $this->token = LibrenmsConfig::get('unimus.token') ?? '';
        $this->enabled = LibrenmsConfig::get('unimus.enabled') === true && $this->base_uri && $this->token;
    }

    public function lastError(): ?string
    {
        return $this->lastError;
    }

    public static function isConfigured(): bool
    {
        return LibrenmsConfig::get('unimus.enabled') === true
            && LibrenmsConfig::has('unimus.url')
            && LibrenmsConfig::has('unimus.token');
    }

    protected function getClient(): \Illuminate\Http\Client\PendingRequest
    {
        return parent::getClient()
            ->acceptJson()
            ->withToken($this->token);
    }

    public function findDeviceId(Device $device): ?int
    {
        if (! $this->enabled) {
            return null;
        }

        $cacheKey = 'unimus:device:' . $device->device_id;
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached === self::NOT_FOUND_SENTINEL ? null : $cached;
        }

        foreach ($this->addressCandidates($device) as $address) {
            $response = $this->get('devices/findByAddress/' . rawurlencode($address));

            if ($response === null) {
                return null; // unreachable, do not cache
            }

            $id = $response->json('data.id');
            if ($response->successful() && is_numeric($id)) {
                Cache::put($cacheKey, (int) $id, self::DEVICE_ID_CACHE_TTL);

                return (int) $id;
            }

            if ($response->status() !== 404) {
                $this->lastError = 'error';

                return null; // auth/server error, do not cache
            }
        }

        Cache::put($cacheKey, self::NOT_FOUND_SENTINEL, self::DEVICE_ID_MISS_TTL);

        return null;
    }

    /**
     * @return array{id: int, date: ?int, until: ?int, type: string, content: ?string}|null
     */
    public function getLatestBackup(int $unimusDeviceId): ?array
    {
        $response = $this->get("devices/$unimusDeviceId/backups/latest");

        if ($response === null || ! $response->successful() || ! is_array($response->json('data'))) {
            return null;
        }

        return $this->normalizeBackup($response->json('data'), include_content: true);
    }

    /**
     * @return array{backups: list<array{id: int, date: ?int, until: ?int, type: string, content: ?string}>, total: int, totalPages: int, page: int}|null
     */
    public function getBackups(int $unimusDeviceId, int $page = 0, int $size = 50): ?array
    {
        $response = $this->get("devices/$unimusDeviceId/backups", ['page' => $page, 'size' => $size]);

        if ($response === null || ! $response->successful() || ! is_array($response->json('data'))) {
            return null;
        }

        return [
            'backups' => array_map(fn ($backup) => $this->normalizeBackup($backup), $response->json('data')),
            'total' => (int) $response->json('paginator.totalCount', 0),
            'totalPages' => (int) $response->json('paginator.totalPages', 1),
            'page' => (int) $response->json('paginator.page', $page),
        ];
    }

    /**
     * Fetch the content of a single backup. Unimus has no fetch-one-backup
     * endpoint, so this retrieves the page of the device's backup list the
     * backup is on (the UI passes the page it was listed on) and extracts it.
     */
    public function getBackupContent(int $unimusDeviceId, int $backupId, int $page = 0, int $size = 50): ?string
    {
        $response = $this->get("devices/$unimusDeviceId/backups", ['page' => $page, 'size' => $size]);

        if ($response === null || ! $response->successful() || ! is_array($response->json('data'))) {
            return null;
        }

        foreach ($response->json('data') as $backup) {
            if (isset($backup['id'], $backup['bytes'])
                && (int) $backup['id'] === $backupId
                && ($backup['type'] ?? 'TEXT') === 'TEXT'
            ) {
                return base64_decode((string) $backup['bytes'], true) ?: null;
            }
        }

        return null;
    }

    /**
     * @return list<array{type: string, original: list<array{line: ?int, text: string}>, revised: list<array{line: ?int, text: string}>}>|null
     */
    public function getDiff(int $origId, int $revId): ?array
    {
        $response = $this->get('backups/diff', ['origId' => $origId, 'revId' => $revId]);

        if ($response === null || ! $response->successful()) {
            return null;
        }

        return $this->normalizeDiff($response->json('data.lineGroups') ?? []);
    }

    /**
     * @param  array<string, mixed>  $backup
     * @return array{id: int, date: ?int, until: ?int, type: string, content: ?string}
     */
    private function normalizeBackup(array $backup, bool $include_content = false): array
    {
        $type = $backup['type'] ?? 'TEXT';
        $content = null;

        if ($include_content && $type === 'TEXT' && isset($backup['bytes'])) {
            $content = base64_decode($backup['bytes'], true) ?: null;
        }

        return [
            'id' => (int) ($backup['id'] ?? 0),
            'date' => isset($backup['validSince']) ? (int) $backup['validSince'] : null,
            'until' => isset($backup['validUntil']) ? (int) $backup['validUntil'] : null,
            'type' => $type,
            'content' => $content,
        ];
    }

    /**
     * @param  array<mixed>  $data
     * @return list<array{type: string, original: list<array{line: ?int, text: string}>, revised: list<array{line: ?int, text: string}>}>
     */
    private function normalizeDiff(array $data): array
    {
        $groups = [];

        foreach ($data as $group) {
            if (! is_array($group) || ! isset($group['type'])) {
                continue;
            }

            $groups[] = [
                'type' => $group['type'],
                'original' => $this->normalizeDiffLines($group['originalLines'] ?? []),
                'revised' => $this->normalizeDiffLines($group['revisedLines'] ?? []),
            ];
        }

        return $groups;
    }

    /**
     * @param  array<mixed>  $lines
     * @return list<array{line: ?int, text: string}>
     */
    private function normalizeDiffLines(array $lines): array
    {
        return array_values(array_map(function ($line) {
            if (is_array($line)) {
                return [
                    'line' => isset($line['number']) ? (int) $line['number'] : null,
                    'text' => (string) ($line['text'] ?? ''),
                ];
            }

            return ['line' => null, 'text' => (string) $line];
        }, $lines));
    }

    /**
     * @return list<string>
     */
    private function addressCandidates(Device $device): array
    {
        $candidates = [
            $device->hostname,
            strtok($device->hostname, '.'),
        ];

        $domain = LibrenmsConfig::get('mydomain');
        if (! empty($domain)) {
            $candidates[] = $device->hostname . '.' . $domain;
        }

        $candidates[] = $device->overwrite_ip ?: $device->ip;

        return array_values(array_unique(array_filter($candidates)));
    }

    /**
     * @param  array<string, int|string>  $query
     */
    private function get(string $uri, array $query = []): ?Response
    {
        if (! $this->enabled) {
            return null;
        }

        $this->lastError = null;

        try {
            return $this->getClient()->get($uri, $query);
        } catch (ConnectionException $e) {
            Log::warning('Unimus is not reachable: ' . $e->getMessage());
            $this->lastError = 'unreachable';

            return null;
        }
    }
}
