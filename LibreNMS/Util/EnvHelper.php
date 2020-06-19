<?php
/**
 * EnvHelper.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use LibreNMS\Exceptions\FileWriteFailedException;

class EnvHelper
{
    /**
     * Set a setting in .env file.
     * Will only set non-empty unset variables
     *
     * @param array $settings KEY => value list of settings
     * @param array $unset Remove the given KEYS from the config
     * @param string $file
     * @return string
     */
    public static function writeEnv($settings, $unset = [], $file = '.env')
    {
        $original_content = file_get_contents($file);

        $new_content = self::setEnv($original_content, $settings, $unset);

        // only write if the content has changed
        if ($new_content !== $original_content) {
            file_put_contents($file, $new_content);
        }

        return $new_content;
    }

    /**
     * Set a setting in .env file.
     * Will only set non-empty unset variables
     *
     * @param array $settings KEY => value list of settings
     * @param array $unset Remove the given KEYS from the config
     * @param string $file
     * @return string
     * @throws \LibreNMS\Exceptions\FileWriteFailedException
     */
    public static function tryWriteEnv($settings, $unset = [], $file = '.env')
    {
        $original_content = file_get_contents($file);

        $new_content = self::setEnv($original_content, $settings, $unset);

        // only write if the content has changed
        if ($new_content !== $original_content) {
            if(!file_put_contents($file, $new_content)) {
                throw new FileWriteFailedException($file);
            }
        }

        return $new_content;
    }

    /**
     * Set a setting in .env file.
     * Will only set non-empty unset variables
     *
     * @param string $content
     * @param array $settings KEY => value list of settings
     * @param array $unset Remove the given KEYS from the config
     * @return string
     */
    public static function setEnv($content, $settings, $unset = [])
    {
        // ensure trailing line return
        if (substr($content, -1) !== PHP_EOL) {
            $content .= PHP_EOL;
        }

        // unset the given keys
        if (!empty($unset)) {
            $regex = '/^(' . implode('|', $unset) . ')=.*$\n/m';
            $content = preg_replace($regex, '', $content);
        }

        foreach ($settings as $key => $value) {
            // only add non-empty settings
            if (empty($value)) {
                continue;
            }

            $value = self::escapeValue($value);

            if (strpos($content, "$key=") !== false) {
                // only replace ones that aren't already set for safety and uncomment
                // escape $ in the replacement
                $content = preg_replace("/#?$key=\n/", addcslashes("$key=$value\n", '$'), $content);
            } else {
                $content .= "$key=$value\n";
            }
        }

        return self::fixComments($content);
    }

    /**
     * Fix .env with # in them without a space before it
     *
     * @param string $dotenv
     * @return string
     */
    private static function fixComments($dotenv)
    {
        return implode(PHP_EOL, array_map(function ($line) {
            $parts = explode('=', $line, 2);
            if (isset($parts[1])
                && preg_match('/(?<!\s)#/', $parts[1]) // number symbol without a space before it
                && !preg_match('/^(".*"|\'.*\')$/', $parts[1]) // not already quoted
            ) {
                return trim($parts[0]) . '="' . trim($parts[1]) . '"';
            }

            return $line;
        }, explode(PHP_EOL, $dotenv)));
    }

    /**
     * quote strings with spaces
     *
     * @param $value
     * @return string
     */
    private static function escapeValue($value)
    {
        if (strpos($value, ' ') !== false) {
            return "\"$value\"";
        }

        return $value;
    }
}
