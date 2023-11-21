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

use App\Models\Device;
use LibreNMS\Config;
use LibreNMS\Util\Http;

class GraylogApi
{
    private \Illuminate\Http\Client\PendingRequest $client;
    private string $api_prefix = '';

    public function __construct()
    {
        if (version_compare(Config::get('graylog.version', '2.4'), '2.1', '>=')) {
            $this->api_prefix = '/api';
        }

        $base_uri = Config::get('graylog.server');
        if ($port = Config::get('graylog.port')) {
            $base_uri .= ':' . $port;
        }

        $this->client = Http::client()
            ->baseUrl($base_uri)
            ->withBasicAuth(Config::get('graylog.username'), Config::get('graylog.password'))
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
     * Query the Graylog server
     */
    public function query(string $query = '*', int $range = 0, int $limit = 0, int $offset = 0, ?string $sort = null, ?string $filter = null): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        $uri = Config::get('graylog.base_uri');
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
     * Build a simple query string that searches the messages field and/or filters by device
     */
    public function buildSimpleQuery(?string $search = null, ?Device $device = null): string
    {
        $field = Config::get('graylog.query.field');
        $query = [];
        if ($search) {
            $query[] = 'message:"' . $search . '"';
        }

        if ($device) {
            $query[] = $field . ': ("' . $this->getAddresses($device)->implode('" OR "') . '")';
        }

        if (empty($query)) {
            return '*';
        }

        return implode(' && ', $query);
    }

    public function getAddresses(Device $device): \Illuminate\Support\Collection
    {
        $addresses = collect([
            gethostbyname($device->hostname),
            $device->hostname,
            $device->displayName(),
            $device->ip,
            $device->sysName,
        ]);

        if (Config::get('graylog.match-any-address')) {
            $addresses = $addresses->merge($device->ipv4->pluck('ipv4_address')
                ->filter(
                    function ($address) {
                        return $address != '127.0.0.1';
                    }
                ))->merge($device->ipv6->pluck('ipv6_address')
                ->filter(
                    function ($address) {
                        return $address != '0000:0000:0000:0000:0000:0000:0000:0001';
                    }
                ));
        }

        return $addresses->filter()->unique();
    }

    public function isConfigured(): bool
    {
        return (bool) Config::get('graylog.server');
    }
}
