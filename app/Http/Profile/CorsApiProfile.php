<?php
/**
 * CorsApiProfile.php
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Profile;

use Illuminate\Support\Arr;
use LibreNMS\Config;
use Spatie\Cors\CorsProfile\DefaultProfile;

class CorsApiProfile extends DefaultProfile
{
    public function addCorsHeaders($response)
    {
        return Config::get('api.cors.enabled') ?
            parent::addCorsHeaders($response) :
            $response;
    }

    public function addPreflightHeaders($response)
    {
        return Config::get('api.cors.enabled') ?
            parent::addPreflightHeaders($response) :
            $response;
    }

    public function allowHeaders(): array
    {
        return Arr::wrap(Config::get('api.cors.allowheaders', []));
    }

    public function allowMethods(): array
    {
        return Arr::wrap(Config::get('api.cors.allowmethods', []));
    }

    public function maxAge(): int
    {
        return (int)Config::get('api.cors.maxage', 86400);
    }

    public function allowOrigins(): array
    {
        return Arr::wrap(Config::get('api.cors.origin', []));
    }

    public function exposeHeaders(): array
    {
        return Arr::wrap(Config::get('api.cors.exposeheaders', []));
    }

    public function allowCredentials(): bool
    {
        return (bool)Config::get('api.cors.allowcredentials');
    }
}
