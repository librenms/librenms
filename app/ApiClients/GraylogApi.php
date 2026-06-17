<?php

/**
 * GraylogApi.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\ApiClients;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use LibreNMS\Util\Http;

class GraylogApi
{
    private readonly \Illuminate\Http\Client\PendingRequest $client;
    private string $api_prefix = '';

    public function __construct()
    {
        if (version_compare(LibrenmsConfig::get('graylog.version', '2.4'), '2.1', '>=')) {
            $this->api_prefix = '/api';
        }

        $base_uri = LibrenmsConfig::get('graylog.server');
        if ($port = LibrenmsConfig::get('graylog.port')) {
            $base_uri .= ':' . $port;
        }

        $this->client = Http::client()
            ->baseUrl($base_uri)
            ->withBasicAuth(LibrenmsConfig::get('graylog.username'), LibrenmsConfig::get('graylog.password'))
            ->acceptJson();
    }

    public function getStreams(): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        $uri = $this->api_prefix . '/streams';

        $response = $this->client->get($uri);

        return $response->json() ?: [];
    }

    /**
     * The stream id every search defaults to: the configured `graylog.default-stream-id`,
     * or the first stream the account can see if that's empty. Avoids unscoped searches
     * (which 403 on limited-access accounts) and keeps the page deterministic.
     */
    public function defaultStreamId(): string
    {
        return LibrenmsConfig::get('graylog.default-stream-id')
            ?: ($this->getStreams()['streams'][0]['id'] ?? '');
    }

    /**
     * Look up a stream by id and return a select2-ready `{id, text}` entry, or null
     * if not found / API unreachable. Shared between the page controllers and the
     * select2 ajax source so the lookup lives in one place.
     */
    public function findStream(string $id): ?array
    {
        if ($id === '') {
            return null;
        }

        try {
            foreach ($this->getStreams()['streams'] ?? [] as $stream) {
                if (($stream['id'] ?? null) === $id) {
                    return [
                        'id' => $stream['id'],
                        'text' => $this->formatStreamText($stream),
                    ];
                }
            }
        } catch (\Exception) {
            // Fall through to null on API failure
        }

        return null;
    }

    public function formatStreamText(array $stream): string
    {
        $text = (string) ($stream['title'] ?? '');
        if (! empty($stream['description'])) {
            $text .= " ({$stream['description']})";
        }

        return $text;
    }

    /**
     * Query the Graylog server
     */
    public function query(string $query = '*', int $range = 0, int $limit = 0, int $offset = 0, ?string $sort = null, ?string $filter = null): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        $uri = LibrenmsConfig::get('graylog.base_uri');
        if (! $uri) {
            $uri = $this->api_prefix . '/search/universal/relative';
        }

        $data = [
            'query' => $query,
            'range' => $range,
            'limit' => $limit,
            'offset' => $offset,
            'sort' => $sort,
            'filter' => $filter,
        ];

        $response = $this->client->get($uri, $data)->throw();

        return $response->json() ?: [];
    }

    /**
     * Build a simple query string. The search term is passed through as a raw
     * Graylog/Lucene query so users can use field qualifiers, wildcards, and
     * boolean operators. A bare term still searches Graylog's default field.
     */
    public function buildSimpleQuery(?string $search = null, ?Device $device = null): string
    {
        $field = LibrenmsConfig::get('graylog.query.field');
        $query = [];
        if ($search) {
            $query[] = '(' . $search . ')';
        }

        if ($device) {
            $query[] = $field . ': ("' . $this->getAddresses($device)->implode('" OR "') . '")';
        }

        if (empty($query)) {
            return '*';
        }

        return implode(' && ', $query);
    }

    /**
     * @return \Illuminate\Support\Collection<int, non-falsy-string>
     */
    public function getAddresses(Device $device): \Illuminate\Support\Collection
    {
        $addresses = collect([
            gethostbyname($device->hostname),
            $device->hostname,
            $device->displayName(),
            $device->ip,
            $device->sysName,
        ]);

        if (LibrenmsConfig::get('graylog.match-any-address')) {
            $addresses = $addresses->merge($device->ipv4->pluck('ipv4_address')
                ->filter(
                    fn ($address) => $address != '127.0.0.1'
                ))->merge($device->ipv6->pluck('ipv6_address')
                ->filter(
                    fn ($address) => $address != '0000:0000:0000:0000:0000:0000:0000:0001'
                ));
        }

        return $addresses->filter()->unique();
    }

    public function isConfigured(): bool
    {
        return (bool) LibrenmsConfig::get('graylog.server');
    }
}
