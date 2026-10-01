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

namespace LibreNMS\RRD;

use App\Facades\LibrenmsConfig;
use LibreNMS\Data\Store\Rrd;

final readonly class RrdPath implements \Stringable
{
    private function __construct(private string $relativePath)
    {
    }

    /**
     * Easy way to start a new instance
     */
    public static function make(string $hostname, string $filename = ''): RrdPath
    {
        return self::fromParts(trim($hostname, '[]'), $filename);
    }

    /**
     * Legacy proxmox layout: proxmox/<cluster>/<filename>
     *
     * @deprecated remove when proxmox is rewritten
     */
    public static function proxmox(string $cluster, string $filename = ''): RrdPath
    {
        return self::fromParts('proxmox', $cluster, $filename);
    }

    /**
     * Sanitize each path component and join them, skipping empty components
     */
    private static function fromParts(string ...$parts): RrdPath
    {
        $parts = array_filter($parts, fn (string $part): bool => $part !== '');

        return new RrdPath(implode(DIRECTORY_SEPARATOR, array_map(Rrd::safeName(...), $parts)));
    }

    public function relativePath(): string
    {
        return $this->relativePath;
    }

    public function fullPath(): string
    {
        return LibrenmsConfig::get('rrd_dir') . DIRECTORY_SEPARATOR . $this->relativePath;
    }

    public function defaultPath(): string
    {
        $rrdcached = LibrenmsConfig::get('rrdcached');

        return ($rrdcached && ! str_starts_with($rrdcached, 'unix:')) ? $this->relativePath() : $this->fullPath();
    }

    public function __toString(): string
    {
        return $this->defaultPath();
    }
}
