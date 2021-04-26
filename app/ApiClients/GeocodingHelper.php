<?php
/**
 * GeocodingHelper.php *
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\ApiClients;

use Exception;
use LibreNMS\Config;
use Log;

trait GeocodingHelper
{
    /**
     * From BaseApi...
     *
     * @return \GuzzleHttp\Client
     */
    abstract protected function getClient();

    /**
     * Try to get the coordinates of a given address.
     * If unsuccessful, the returned array will be empty
     *
     * @param string $address
     * @return array ['lat' => 0, 'lng' => 0]
     */
    public function getCoordinates($address)
    {
        if (! Config::get('geoloc.latlng', true)) {
            Log::debug('Geocoding disabled');

            return [];
        }

        try {
            $options = $this->buildGeocodingOptions($address);

            $response = $this->getClient()->get($this->geocoding_uri, $options);
            $response_data = json_decode($response->getBody(), true);
            if ($this->checkResponse($response, $response_data)) {
                return $this->parseLatLng($response_data);
            } else {
                Log::error('Geocoding failed.', ['response' => $response_data]);
            }
        } catch (Exception $e) {
            Log::error('Geocoding failed: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Checks if the request was a success
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array $data decoded response data
     * @return bool
     */
    protected function checkResponse($response, $data)
    {
        return $response->getStatusCode() == 200;
    }

    /**
     * Get latitude and longitude from geocode response
     *
     * @param array $data
     * @return array
     */
    abstract protected function parseLatLng($data);

    /**
     * Build Guzzle request option array
     *
     * @param string $address
     * @return array
     * @throws \Exception you may throw an Exception if validation fails
     */
    abstract protected function buildGeocodingOptions($address);
}
