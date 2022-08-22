<?php
/*
 * Proxy.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use LibreNMS\Config;

class Proxy
{
    /**
     * Check if if the proxy should be used.
     * (it should not be used for connections to localhost)
     */
    public static function shouldBeUsed(string $target_url): bool
    {
        return preg_match('#(^|://)(localhost|127\.|::1)#', $target_url) == 0;
    }

    /**
     * Return the proxy url
     *
     * @return array|bool|false|string
     */
    public static function get(?string $target_url = null)
    {
        if ($target_url && ! self::shouldBeUsed($target_url)) {
            return false;
        } elseif (getenv('http_proxy')) {
            return getenv('http_proxy');
        } elseif (getenv('https_proxy')) {
            return getenv('https_proxy');
        } elseif ($callback_proxy = Config::get('callback_proxy')) {
            return $callback_proxy;
        } elseif ($http_proxy = Config::get('http_proxy')) {
            return $http_proxy;
        }

        return false;
    }

    /**
     * Return the proxy url in guzzle format "http://127.0.0.1:8888"
     */
    public static function forGuzzle(?string $target_url = null): string
    {
        $proxy = self::forCurl($target_url);

        return empty($proxy) ? '' : ('http://' . $proxy);
    }

    /**
     * Get the ip and port of the proxy
     *
     * @return string
     */
    public static function forCurl(?string $target_url = null): string
    {
        return str_replace(['http://', 'https://'], '', rtrim(self::get($target_url), '/'));
    }

    /**
     * Set the proxy on a curl handle
     *
     * @param  resource  $curl
     */
    public static function applyToCurl($curl): void
    {
        $proxy = self::forCurl();
        if (! empty($proxy)) {
            curl_setopt($curl, CURLOPT_PROXY, $proxy);
        }
    }
}
