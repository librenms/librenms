<?php

/**
 * LocationState.php
 *
 * How likely a forwarding table entry is the port the host is plugged into
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Search\Data;

enum LocationState
{
    /** the only MAC on the port */
    case Direct;
    /** one of several MACs on the port */
    case Shared;
    /** no port stands out: links to other network devices and aggregates */
    case Trunk;
    /** not in a forwarding table */
    case Unknown;

    /**
     * The colour of its labels and status bars
     */
    public function labelClass(): string
    {
        return match ($this) {
            self::Direct => 'label-success',
            self::Shared => 'label-default',
            self::Trunk, self::Unknown => 'label-warning',
        };
    }
}
