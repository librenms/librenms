<?php
/**
 * SnmpOptions.php
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Source;

class SnmpQueryOptions
{
    public function __construct(
        public readonly bool $numeric = false,
        public readonly bool $numericIndex = false,
        public readonly bool $hideMib = false,
        public readonly bool $enumStrings = true,
        public readonly bool $allowUnordered = false,
        public readonly ?int $maxRepeaters = null,
        public readonly ?int $timeout = null,
        public readonly ?int $retries = null,
        public readonly array $mibs = [],
        public readonly array $mibDirs = [],
    ) {}
}
