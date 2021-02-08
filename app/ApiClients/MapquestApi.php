<?php
/**
 * MapquestGeocodeApi.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\ApiClients;

use Exception;
use LibreNMS\Config;
use LibreNMS\Interfaces\Geocoder;

class MapquestApi extends BaseApi implements Geocoder
{
    use GeocodingHelper;

    protected $base_uri = 'https://open.mapquestapi.com';
    protected $geocoding_uri = '/geocoding/v1/address';

    /**
     * Get latitude and longitude from geocode response
     *
     * @param array $data
     * @return array
     */
    protected function parseLatLng($data)
    {
        return [
            'lat' => isset($data['results'][0]['locations'][0]['latLng']['lat']) ? $data['results'][0]['locations'][0]['latLng']['lat'] : 0,
            'lng' => isset($data['results'][0]['locations'][0]['latLng']['lng']) ? $data['results'][0]['locations'][0]['latLng']['lng'] : 0,
        ];
    }

    /**
     * Build Guzzle request option array
     *
     * @param string $address
     * @return array
     * @throws \Exception you may throw an Exception if validation fails
     */
    protected function buildGeocodingOptions($address)
    {
        $api_key = Config::get('geoloc.api_key');
        if (! $api_key) {
            throw new Exception('MapQuest API key missing, set geoloc.api_key');
        }

        return [
            'query' => [
                'key' => $api_key,
                'location' => $address,
                'thumbMaps' => 'false',
            ],
        ];
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
        return $response->getStatusCode() == 200 && $data['info']['statuscode'] == 0;
    }
}
