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
use Log;

final class RrdPath implements \Stringable
{
    private readonly string $rrdDir;
    private readonly string $relativeDir;
    private readonly string $fileName;

    private function __construct(string $hostname, string $filename)
    {
        $this->fileName = self::safeName($filename);
        $this->relativeDir = self::safeName(trim($hostname, '[]'));
        $this->rrdDir = LibrenmsConfig::get('rrd_dir', base_path('rrd'));
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
        return $this->fileName($this->fullDir());
    }

    public function defaultPath(): string
    {
        return $this->fileName($this->defaultDir());
    }

    public function __toString(): string
    {
        return $this->defaultPath();
    }

    /**
     * Check that the directories exist if we are not using rrdcached
     */
    public function checkDirExists(): RrdPath
    {
        if (LibrenmsConfig::get('rrdcached')) {
            return $this;
        }

        $rrd_dir = $this->fullDir();
        if (! is_dir($rrd_dir)) {
            if (mkdir($rrd_dir, 0775, true)) {
                Log::info("Created directory : $rrd_dir");
            } else {
                Log::error("Failed to create rrd directory: $rrd_dir");
            }
        }

        return $this;
    }

    /**
     * Replaces invalid characters in a path with underscores
     */
    public static function safeName(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9,._\-]/', '_', $name);
    }

    /**
     * Return the directory component for a full path
     */
    public function fullDir(): string
    {
        return $this->rrdDir . DIRECTORY_SEPARATOR . $this->relativeDir;
    }

    /**
     * Return the default directory component depending on whether rrdcached is enabled or not
     */
    public function defaultDir(): string
    {
        return LibrenmsConfig::get('rrdcached') ? $this->relativeDir : $this->fullDir();
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
