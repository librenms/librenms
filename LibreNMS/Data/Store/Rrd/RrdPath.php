<?php

/**
 * RrdPath.php
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
 * @copyright  2026 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.au>
 */

namespace LibreNMS\Data\Store\Rrd;

use App\Facades\LibrenmsConfig;
use LibreNMS\Data\Store\Rrd;

final readonly class RrdPath implements \Stringable
{
    private string $relativeDir;
    private string $fileName;

    private function __construct(string $hostname, string $filename)
    {
        $this->fileName = Rrd::safeName($filename);
        $this->relativeDir = Rrd::safeName(trim($hostname, '[]'));
    }

    /**
     * Easy way to start a new instance
     */
    public static function make(string $hostname, string $filename = ''): RrdPath
    {
        return new RrdPath($hostname, $filename);
    }

    public function relativePath(): string
    {
        return $this->fileName($this->relativeDir);
    }

    public function fullPath(): string
    {
        return $this->fileName(LibrenmsConfig::get('rrd_dir') . DIRECTORY_SEPARATOR . $this->relativeDir);
    }

    public function defaultPath(): string
    {
        return LibrenmsConfig::get('rrdcached') ? $this->relativePath() : $this->fullPath();
    }

    public function __toString(): string
    {
        return $this->defaultPath();
    }

    /**
     * Builds the filename for the current path
     */
    private function fileName(string $basedir): string
    {
        if (! $this->fileName) {
            return $basedir;
        }

        return $basedir . DIRECTORY_SEPARATOR . $this->fileName;
    }
}
