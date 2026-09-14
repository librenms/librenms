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

final class RrdPath {
    private readonly string $relativePath;
    private readonly string $rrdDir;
    private string $fileName = '';

    /**
     * @param  string|string[]  $hostname  hostname of the device
     */
    public function __construct(string|array $hostname) {
        if (is_array($hostname)) {
            $hostname = implode(DIRECTORY_SEPARATOR, $hostname);
        }

        $this->relativePath = self::safeName(trim($hostname, '[]'));
        $this->rrdDir = LibrenmsConfig::get('rrd_dir', base_path('rrd'));
    }

    /**
     * Easy way to start a new instance
     * @param  string|string[]  $hostname  hostname of the device
     */
    public static function make(string|array $hostname): RrdPath
    {
        return new RrdPath($hostname);
    }

    public function relativePath(): string
    {
        return $this->relativePath;
    }
  
    public function fullPath(): string
    {
        return $this->rrdDir . DIRECTORY_SEPARATOR . $this->relativePath;
    }

    public function defaultPath(): string
    {
        return LibrenmsConfig::get('rrdcached') ? $this->relativePath() : $this->fullPath();
    }

    public function relativeFilePath(): string
    {
        return $this->fileName($this->relativePath);
    }
  
    public function fullFilePath(): string
    {
        return $this->fileName($this->fullPath());
    }

    public function defaultFilePath(): string
    {
        return $this->fileName($this->defaultPath());
    }

    public function __toString(): string
    {
        return $this->defaultFilePath();
    }

    /**
     * @param  string|string[]  $file  filename or array of parts to build the filename
     */
    public function setFileName(string|array $filename, string $suffix = ''): RrdPath
    {
        $this->fileName = self::safeName(is_array($filename) ? implode('-', $filename) : $filename) . $suffix;

        return $this;
    }

    /**
     * Check that the directories exist if we are not using rrdcached
     */
    public function checkDirExists(): RrdPath
    {
        if (LibrenmsConfig::get('rrdcached')) {
            return $this;
        }

        if (! is_dir($this->fullPath())) {
            mkdir($this->fullPath(), 0775, true);
            Log::info('Created directory : ' . $this->fullPath());
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
