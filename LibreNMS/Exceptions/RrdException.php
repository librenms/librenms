<?php

/**
 * RrdException.php
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
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Exceptions;

abstract class RrdException extends \Exception
{
    /**
     * Map a librrd error message (rrdtool output or php-rrd's rrd_error()) to an exception.
     * Backends with their own error format should strip it before calling this.
     */
    public static function parse(string $message): self
    {
        if (preg_match('/ERROR: (.*)/', $message, $matches)) {
            $message = $matches[1];
        }
        $error = trim($message);

        if (str_contains($error, 'Unable to connect to rrdcached')) {
            return new RrdCachedConnectionException($error);
        }

        if (str_contains($error, 'No such file')) {
            return new RrdNotFoundException($error);
        }

        if (str_contains($error, 'File exists')) {
            return new RrdFileExistsException($error);
        }

        if (str_contains($error, 'illegal attempt to update using time')) {
            return new RrdUpdateTooFrequentException($error);
        }

        if (str_contains($error, 'expected') && str_contains($error, 'data source readings')) {
            return new RrdDsMismatchException($error);
        }

        if (str_contains($error, 'unknown DS name')) {
            return new RrdDsMismatchException($error);
        }

        if (str_contains($error, 'found extra data on update argument')) {
            return new RrdDsMismatchException($error);
        }

        if (str_contains($error, 'expected timestamp not found in data source')) {
            return new RrdDsMismatchException($error);
        }

        if (str_contains($error, 'Permission denied') || str_contains($error, 'Cannot create temporary file')) {
            return new RrdPermissionException($error);
        }

        if (str_contains($error, 'reached EOF while loading header')) {
            return new RrdCorruptionException($error);
        }

        return new RrdUnknownException($message);
    }
}
