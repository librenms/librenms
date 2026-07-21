<?php

/**
 * Oxidized.php
 *
 * -Description-
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\ApiClients;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

class Oxidized extends BaseApi
{
    private readonly bool $enabled;
    private ?string $lastError = null;

    public function __construct()
    {
        $this->timeout = 90;
        $this->base_uri = LibrenmsConfig::get('oxidized.url') ?? '';
        $this->enabled = LibrenmsConfig::get('oxidized.enabled') === true && $this->base_uri;
    }

    /**
     * Whether Oxidized is enabled and has a URL configured. Used by the
     * ConfigBackupProvider to decide if it can serve the device Config tab.
     */
    public static function isConfigured(): bool
    {
        return LibrenmsConfig::get('oxidized.enabled') === true
            && LibrenmsConfig::has('oxidized.url');
    }

    /**
     * The last transport error ('unreachable'), or null. Reset on each request.
     */
    public function lastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Look up a node in Oxidized, trying the device's hostname variants and IP.
     * Returns the decoded node info (name, full_name, group, ...) or null if the
     * node is unknown or Oxidized is unreachable (check lastError() to tell them
     * apart).
     *
     * @return array<string, mixed>|null
     */
    public function findNode(Device $device): ?array
    {
        if (! $this->enabled) {
            return null;
        }

        foreach ($this->hostnameCandidates($device) as $host) {
            $response = $this->request('/node/show/' . rawurlencode($host) . '?format=json');

            if ($response === null) {
                return null; // unreachable, do not keep trying
            }

            if ($response->successful()) {
                $info = $response->json();
                if (is_array($info) && ! empty($info['name'])) {
                    return $info;
                }
            }
        }

        return null;
    }

    /**
     * Fetch the version history for a node. Returns null when Oxidized is
     * unreachable or versioning is disabled (a non-2xx response); check
     * lastError() to distinguish.
     *
     * @return list<array<string, mixed>>|null
     */
    public function getVersions(string $nodeFull): ?array
    {
        $response = $this->request('/node/version?node_full=' . rawurlencode($nodeFull) . '&format=json');

        if ($response === null || ! $response->successful()) {
            return null;
        }

        $data = $response->json();

        return is_array($data) ? array_values($data) : null;
    }

    /**
     * Fetch the config for a single version (git oid) of a node.
     */
    public function getVersionContent(string $node, string $oid): ?string
    {
        $response = $this->request('/node/version/view?node=' . rawurlencode($node) . '&oid=' . rawurlencode($oid) . '&format=text');

        if ($response === null || ! $response->successful()) {
            return null;
        }

        return $response->body();
    }

    /**
     * Fetch the current config for a node (used when versioning is disabled).
     */
    public function getNodeConfig(string $node, ?string $group = null): ?string
    {
        $path = '/node/fetch/' . ($group ? rawurlencode($group) . '/' : '') . rawurlencode($node);
        $response = $this->request($path);

        if ($response === null || ! $response->successful()) {
            return null;
        }

        return $response->body();
    }

    /**
     * Fetch the unified diff between two versions. Oxidized treats oid as the
     * newer (revised) side and oid2 as the older (original) side.
     */
    public function getDiff(string $node, string $newOid, string $oldOid): ?string
    {
        $response = $this->request('/node/version/diffs?node=' . rawurlencode($node)
            . '&oid=' . rawurlencode($newOid)
            . '&oid2=' . rawurlencode($oldOid)
            . '&format=text');

        if ($response === null || ! $response->successful()) {
            return null;
        }

        return $response->body();
    }

    /**
     * Ask oxidized to refresh the node list for the source (likely the LibreNMS API).
     */
    public function reloadNodes(): void
    {
        if ($this->enabled && LibrenmsConfig::get('oxidized.reload_nodes') === true) {
            try {
                $this->getClient()->get('/reload.json');
            } catch (ConnectionException $e) {
                Log::warning('Oxidized is not reachable: ' . $e->getMessage());
            }
        }
    }

    /**
     * Queues a hostname to be refreshed by Oxidized
     */
    public function updateNode(string $hostname, string $msg, string $username = 'not_provided'): bool
    {
        if ($this->enabled) {
            // Work around https://github.com/rack/rack/issues/337
            $msg = str_replace('%', '', $msg);

            try {
                return $this->getClient()
                    ->put("/node/next/$hostname", ['user' => $username, 'msg' => $msg])
                    ->successful();
            } catch (ConnectionException $e) {
                Log::warning('Oxidized is not reachable: ' . $e->getMessage());
            }
        }

        return false;
    }

    /* Get content of the page */
    public function getContent(string $uri): string
    {
        if ($this->enabled) {
            try {
                return $this->getClient()->get($uri);
            } catch (ConnectionException $e) {
                Log::warning('Oxidized is not reachable: ' . $e->getMessage());

                return '';
            }
        }

        return '';
    }

    /**
     * Perform a GET, returning null (and recording lastError) when Oxidized is
     * disabled or unreachable.
     */
    private function request(string $uri): ?Response
    {
        if (! $this->enabled) {
            return null;
        }

        $this->lastError = null;

        try {
            return $this->getClient()->get($uri);
        } catch (ConnectionException $e) {
            Log::warning('Oxidized is not reachable: ' . $e->getMessage());
            $this->lastError = 'unreachable';

            return null;
        }
    }

    /**
     * Hostname variants (and IP) to try when matching a device to an Oxidized
     * node, mirroring the legacy Config tab lookup order.
     *
     * @return list<string>
     */
    private function hostnameCandidates(Device $device): array
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
}
