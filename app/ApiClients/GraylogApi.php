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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\ApiClients;

use App\Models\Device;
use GuzzleHttp\Client;
use LibreNMS\Config;

class GraylogApi
{
    private $client;
    private $api_prefix = '';

    public function __construct(array $config = [])
    {
        if (version_compare(Config::get('graylog.version', '2.4'), '2.1', '>=')) {
            $this->api_prefix = '/api';
        }

        if (empty($config)) {
            $base_uri = Config::get('graylog.server');
            if ($port = Config::get('graylog.port')) {
                $base_uri .= ':' . $port;
            }

            $config = [
                'base_uri' => $base_uri,
                'auth' => [Config::get('graylog.username'), Config::get('graylog.password')],
                'headers' => ['Accept' => 'application/json'],
            ];
        }

        $this->client = new Client($config);
    }

    public function getStreams()
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $uri = $this->api_prefix . '/streams';

        $response = $this->client->get($uri);
        $data = json_decode($response->getBody(), true);

        return $data ?: [];
    }

    /**
     * Query the Graylog server
     *
     * @param string $query
     * @param int $range
     * @param int $limit
     * @param int $offset
     * @param string $sort field:asc or field:desc
     * @param string $filter
     * @return array
     */
    public function query($query = '*', $range = 0, $limit = 0, $offset = 0, $sort = null, $filter = null)
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $uri = Config::get('graylog.base_uri');
        if (!$uri) {
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

        $response = $this->client->get($uri, ['query' => $data]);
        $data = json_decode($response->getBody(), true);

        return $data ?: [];
    }

    /**
     * Build a simple query string that searches the messages field and/or filters by device
     *
     * @param string $search Search the message field for this string
     * @param Device $device
     * @return string
     */
    public function buildSimpleQuery($search = null, $device = null)
    {
        $query = [];
        if ($search) {
            $query[] = 'message:"' . $search . '"';
        }

        if ($device) {
            $ip = gethostbyname($device->hostname);
            $device_query = 'source:"' . $device->hostname . '" || source:"' . $ip . '"';
            if ($device->ip && $ip != $device->ip) {
                $query .= ' || source:"' . $device->ip . '"';
            }

            $query[] = '(' . $device_query . ')';
        }

        if (empty($query)) {
            return '*';
        }

        return implode('&&', $query);
    }

    public function isConfigured()
    {
        return isset($this->client->getConfig()['base_uri']);
    }
}
