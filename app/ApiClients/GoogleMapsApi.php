<?php
/**
 * GoogleGeocodeApi.php
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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use LibreNMS\Config;
use LibreNMS\Interfaces\Geocoder;
use Log;

class GoogleMapsApi implements Geocoder
{
    private $client;

    private $base_uri = 'https://maps.googleapis.com';
    private $geocoding_uri = '/maps/api/geocode/json';

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => $this->base_uri,
            'tiemout' => 2,
        ]);

        Log::debug('Google Maps geocode engine being used');
    }

    /**
     * Try to get the coordinates of a given address.
     * If unsuccessful, the returned array will be empty
     *
     * @param string $address
     * @return array ['lat' => 0, 'lng' => 0]
     */
    public function getCoordinates($address)
    {
        if (!Config::get('geoloc.latlng')) {
            Log::debug('Geocoding disabled');
            return [];
        }

        $api_key = Config::get('geoloc.api_key');
        if (!$api_key) {
            Log::debug('Google Maps API key missing, set geoloc.api_key');
            return [];
        }

        $options = [
            'query' => [
                'key' => $api_key,
                'address' => $address,
            ]
        ];

        try {
            $response = $this->client->get($this->geocoding_uri, $options);
            $response_data = json_decode($response->getBody(), true);
            if ($response->getReasonPhrase() == 'OK') {
                return $this->parseLatLng($response_data);
            } else {
                Log::error("Geocoding failed.", [
                    'error' => isset($response_data['error_message']) ? $response_data['error_message'] : '',
                    'response' => $response_data,
                ]);
            }
        } catch (TransferException $e) {
            Log::error("Geocoding failed: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Get latitude and longitude from geocode response
     *
     * @param array $data
     * @return array
     */
    private function parseLatLng($data)
    {
        return [
            'lat' => isset($data['results'][0]['geometry']['location']['lat']) ? $data['results'][0]['geometry']['location']['lat'] : null,
            'lng' => isset($data['results'][0]['geometry']['location']['lng']) ? $data['results'][0]['geometry']['location']['lng'] : null,
        ];
    }
}
