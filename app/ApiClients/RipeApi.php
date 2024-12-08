<?php
/**
 * RipeWhoisApi.php
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\ApiClients;

use LibreNMS\Exceptions\ApiClientException;

class RipeApi extends BaseApi
{
    protected string $base_uri = 'https://stat.ripe.net';

    protected string $whois_uri = '/data/whois/data.json';
    protected string $abuse_uri = '/data/abuse-contact-finder/data.json';

    /**
     * Get whois info
     *
     * @throws ApiClientException
     */
    public function getWhois(string $resource): array
    {
        return $this->makeApiCall($this->whois_uri, [
            'query' => [
                'resource' => $resource,
            ],
        ]);
    }

    /**
     * Get Abuse contact
     *
     * @throws ApiClientException
     */
    public function getAbuseContact(string $resource): mixed
    {
        return $this->makeApiCall($this->abuse_uri, [
            'query' => [
                'resource' => $resource,
            ],
        ]);
    }

    /**
     * @throws ApiClientException
     */
    private function makeApiCall(string $uri, array $options): mixed
    {
        $response_data = $this->getClient()->get($uri, $options['query'])->json();

        if (isset($response_data['status']) && $response_data['status'] == 'ok') {
            return $response_data;
        }

        throw new ApiClientException("RIPE API call to $this->base_uri/$uri failed: " . $this->getClient()->get($uri, $options['query'])->status(), $response_data);
    }
}
