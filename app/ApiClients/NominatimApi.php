<?php
/**
 * NominatimApi.php
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

use LibreNMS\Interfaces\Geocoder;

class NominatimApi extends BaseApi implements Geocoder
{
    use GeocodingHelper;

    protected $base_uri = 'http://nominatim.openstreetmap.org';
    protected $geocoding_uri = '/search';

    /**
     * Get latitude and longitude from geocode response
     *
     * @param array $data
     * @return array
     */
    protected function parseLatLng($data)
    {
        return [
            'lat' => isset($data[0]['lat']) ? $data[0]['lat'] : 0,
            'lng' => isset($data[0]['lon']) ? $data[0]['lon'] : 0,
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
        return [
            'query' => [
                'q' => $address,
                'format' => 'json',
                'limit' => 1,
            ],
            'headers' => [
                'User-Agent' => 'LibreNMS',
                'Accept'     => 'application/json',
            ],
        ];
    }
}
