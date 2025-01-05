<?php
/**
 * BingApi.php
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

use Exception;
use Illuminate\Http\Client\Response;
use LibreNMS\Config;
use LibreNMS\Interfaces\Geocoder;

class BingApi extends BaseApi implements Geocoder
{
    use GeocodingHelper;

    protected string $base_uri = 'http://dev.virtualearth.net';
    protected string $geocoding_uri = '/REST/v1/Locations';

    /**
     * Get latitude and longitude from geocode response
     */
    protected function parseLatLng(array $data): array
    {
        return [
            'lat' => isset($data['resourceSets'][0]['resources'][0]['point']['coordinates'][0]) ? $data['resourceSets'][0]['resources'][0]['point']['coordinates'][0] : 0,
            'lng' => isset($data['resourceSets'][0]['resources'][0]['point']['coordinates'][1]) ? $data['resourceSets'][0]['resources'][0]['point']['coordinates'][1] : 0,
        ];
    }

    /**
     * Build request option array
     *
     * @throws \Exception you may throw an Exception if validation fails
     */
    protected function buildGeocodingOptions(string $address): array
    {
        $api_key = Config::get('geoloc.api_key');
        if (! $api_key) {
            throw new Exception('Bing API key missing, set geoloc.api_key');
        }

        return [
            'query' => [
                'key' => $api_key,
                'addressLine' => $address,
            ],
        ];
    }

    /**
     * Checks if the request was a success
     */
    protected function checkResponse(Response $response, array $data): bool
    {
        return $response->successful() && ! empty($data['resourceSets'][0]['resources']);
    }
}
