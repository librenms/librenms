<?php
/**
 * Env.php
 *
 * Helpers to interact with environment variables.
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

namespace LibreNMS\Util;

class Env
{
    /**
     * Parse comma separated environment variable into an array.
     *
     * @param string $env_name
     * @param mixed $default
     * @param array $except Ignore these values and return the unexploded string
     * @return array|mixed
     */
    public static function parseArray($env_name, $default = null, $except = [''])
    {
        $value = env($env_name, $default);

        if (is_string($value) && !in_array($value, $except)) {
            $value = explode(',', $value);
        }

        return $value;
    }
}
