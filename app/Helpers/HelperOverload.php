<?php
/**
 * HelperOverload.php
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

// override the default route helper to provide support for proxy with subdir and app without
if (!function_exists('route')) {
    /**
     * Generate the URL to a named route.
     *
     * @param  string  $name
     * @param  array   $parameters
     * @param  bool    $absolute
     * @return string
     */
    function route($name, $parameters = [], $absolute = true)
    {
        $appUrlSuffix = config('app.url_suffix');

        // Additional check, do the workaround only when a suffix is present and only when urls are absolute
        if ($appUrlSuffix && $absolute) {
            $appUrl = config('app.url'); // in your case: http://app.dev

            // Add the relative path to the app root url
            $relativePath = app('url')->route($name, $parameters, false);
            $url = $appUrl.$relativePath;
        } else {
            // This is the default behavior of route() you can find in laravel\vendor\laravel\framework\src\Illuminate\Foundation\helpers.php
            $url = app('url')->route($name, $parameters, $absolute);
        }

        return $url;
    }
}
