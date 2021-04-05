<?php
/**
 * Clean.php
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use HTMLPurifier;
use HTMLPurifier_Config;
use LibreNMS\Config;

class Clean
{
    /**
     * Sanitize file name by removing all invalid characters.
     * Does not make the string safe for javascript or sql!
     *
     * @param string $file
     * @return string|string[]|null
     */
    public static function fileName($file)
    {
        return preg_replace('/[^a-zA-Z0-9\-._]/', '', $file);
    }

    /**
     * Sanitize string to only contain alpha, numeric, dashes, and underscores
     *
     * @param string $string
     * @return string
     */
    public static function alphaDash($string)
    {
        return preg_replace('/[^a-zA-Z0-9\-_]/', '', $string);
    }

    /**
     * Sanitize string to only contain alpha, numeric, dashes, underscores, and spaces
     *
     * @param string $string
     * @return string
     */
    public static function alphaDashSpace($string)
    {
        return preg_replace('/[^a-zA-Z0-9\-_ ]/', '', $string);
    }

    /**
     * Clean a string for display in an html page.
     * For use in non-blade pages
     *
     * @param string $value
     * @param array $purifier_config (key, value pair)
     * @return string
     */
    public static function html($value, $purifier_config = [])
    {
        /** @var HTMLPurifier $purifier */
        static $purifier;

        // If $purifier_config is non-empty then we don't want
        // to convert html tags and allow these to be controlled
        // by purifier instead.
        if (empty($purifier_config)) {
            $value = htmlentities($value);
        }

        if (! $purifier instanceof HTMLPurifier) {
            // initialize HTML Purifier here since this is the only user
            $p_config = HTMLPurifier_Config::createDefault();
            $p_config->set('Cache.SerializerPath', Config::get('temp_dir', '/tmp'));
            foreach ($purifier_config as $k => $v) {
                $p_config->set($k, $v);
            }
            $purifier = new HTMLPurifier($p_config);
        }

        return $purifier->purify(stripslashes($value));
    }
}
