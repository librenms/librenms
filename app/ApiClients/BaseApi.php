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

use LibreNMS\Util\Http;

class BaseApi
{
    protected string $base_uri = '';
    protected int $timeout = 3;
    private ?\Illuminate\Http\Client\PendingRequest $client = null;

    protected function getClient(): \Illuminate\Http\Client\PendingRequest
    {
        if (is_null($this->client)) {
            $this->client = Http::client()->baseUrl($this->base_uri)
            ->timeout($this->timeout);
        }

        return $this->client;
    }
}
