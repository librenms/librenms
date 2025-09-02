<?php

/**
 * File.php
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

namespace LibreNMS\Util;

use Illuminate\Support\Facades\File as LaravelFile;

class File
{
    /**
     * @param  string  $directory
     * @return array{int, int} size, count
     */
    public static function getFolderSize(string $directory): array
    {
        $totalSize = 0;
        $files = LaravelFile::isDirectory($directory) ? LaravelFile::allFiles($directory) : [];

        foreach ($files as $file) {
            $totalSize += $file->getSize();
        }

        return [$totalSize, count($files)];
    }
}
