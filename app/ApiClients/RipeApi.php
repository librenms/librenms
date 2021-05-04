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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\ApiClients;

use GuzzleHttp\Exception\RequestException;
use LibreNMS\Exceptions\ApiException;

class RipeApi extends BaseApi
{
    protected $base_uri = 'https://stat.ripe.net';

    protected $whois_uri = '/data/whois/data.json';
    protected $abuse_uri = '/data/abuse-contact-finder/data.json';

    /**
     * Get whois info
     *
     * @param string $resource ASN/IPv4/IPv6
     * @return array
     * @throws ApiException
     */
    public function getWhois($resource)
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
     * @param string $resource prefix, single IP address or ASN
     * @return array|mixed
     * @throws ApiException
     */
    public function getAbuseContact($resource)
    {
        return $this->makeApiCall($this->abuse_uri, [
            'query' => [
                'resource' => $resource,
            ],
        ]);
    }

    /**
     * @return array|mixed
     * @throws ApiException
     */
    private function makeApiCall(string $uri, array $options)
    {
        try {
            $response = $this->getClient()->get($uri, $options);
            $response_data = json_decode($response->getBody(), true);
            if (isset($response_data['status']) && $response_data['status'] == 'ok') {
                return $response_data;
            } else {
                throw new ApiException('RIPE API call failed', $response_data);
            }
        } catch (RequestException $e) {
            $message = 'RIPE API call to ' . $e->getRequest()->getUri() . ' failed: ';
            $message .= $e->getResponse()->getReasonPhrase() . ' ' . $e->getResponse()->getStatusCode();

            throw new ApiException(
                $message,
                json_decode($e->getResponse()->getBody(), true)
            );
        }
    }
}
