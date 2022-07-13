<?php
/**
 * BaseApi.php
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

use Illuminate\Support\Facades\Http;
use LibreNMS\Util\Proxy;

class BaseApi
{
    protected $base_uri;
    private $client;

    protected function getClient(): \Illuminate\Http\Client\PendingRequest
    {
        if (is_null($this->client)) {
            $this->client = Http::withOptions([
                'proxy' => Proxy::forGuzzle($this->base_uri),
            ])->baseUrl($this->base_uri)
            ->timeout(3);
        }

        return $this->client;
    }
}
