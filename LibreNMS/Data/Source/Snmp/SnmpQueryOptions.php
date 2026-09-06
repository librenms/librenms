<?php

/**
 * SnmpQueryOptions.php
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

namespace LibreNMS\Data\Source\Snmp;

use Illuminate\Support\Arr;

class SnmpQueryOptions
{
    public function __construct(
        public string $context = '',
        public array $mibs = ['SNMPv2-TC', 'SNMPv2-MIB', 'IF-MIB', 'IP-MIB', 'TCP-MIB', 'UDP-MIB', 'NET-SNMP-VACM-MIB'],
        public array $mibDirs = [],

        // Query behavior
        public bool $allowBulk = true,
        public bool $tolerateUnorderedIndexes = false,

        // OID formatting
        public bool $numericOids = false,
        public bool $numericIndexes = false,
        public bool $outputMibNames = true,

        // Value formatting
        public bool $numericEnums = true,
        public bool $numericTimeticks = true,
        public bool $printUnits = false,
        public bool $applyDisplayHints = true,

        // Output presentation
        public bool $quickPrint = true,
        public bool $extendedIndex = true,
        public bool $allowUnderlines = false,
    ) {}

    public function parseCli(array|string|null $flags): self
    {
        if ($flags === null) {
            $this->context = '';
            $this->allowBulk = true;
            $this->tolerateUnorderedIndexes = false;
            $this->numericOids = false;
            $this->numericIndexes = false;
            $this->outputMibNames = true;
            $this->numericEnums = true;
            $this->numericTimeticks = true;
            $this->printUnits = false;
            $this->applyDisplayHints = true;
            $this->quickPrint = true;
            $this->extendedIndex = true;
            $this->allowUnderlines = false;

            return $this;
        }

        $wrapped = Arr::wrap($flags);
        $hasCustomOutputFlags = false;

        for ($i = 0; $i < count($wrapped); $i++) {
            $flag = $wrapped[$i];
            if (! is_string($flag)) {
                continue;
            }

            if (str_starts_with($flag, '-O')) {
                $hasCustomOutputFlags = true;
                $opts = substr($flag, 2);
                $this->quickPrint = str_contains($opts, 'Q') || str_contains($opts, 'q');
                $this->extendedIndex = str_contains($opts, 'X');
                $this->printUnits = ! str_contains($opts, 'U');
                $this->numericTimeticks = str_contains($opts, 't');
                $this->numericOids = str_contains($opts, 'n');
                $this->numericIndexes = str_contains($opts, 'b');
                $this->outputMibNames = ! str_contains($opts, 's');
                $this->numericEnums = str_contains($opts, 'e');
            } elseif (str_starts_with($flag, '-C')) {
                $opts = substr($flag, 2);
                if (str_contains($opts, 'c') || str_contains($opts, 'i')) {
                    $this->tolerateUnorderedIndexes = true;
                }
            } elseif (str_starts_with($flag, '-P')) {
                $opts = substr($flag, 2);
                $this->allowUnderlines = ! str_contains($opts, 'u');
            } elseif (str_starts_with($flag, '-I')) {
                $opts = substr($flag, 2);
                if (str_contains($opts, 'h')) {
                    $this->applyDisplayHints = false;
                }
            } elseif ($flag === '-m' && isset($wrapped[$i + 1])) {
                $mib = $wrapped[++$i];
                if (is_string($mib)) {
                    if (str_starts_with($mib, '+')) {
                        $this->mibs[] = substr($mib, 1);
                        $this->mibs = array_values(array_unique($this->mibs));
                    } else {
                        $this->mibs = explode(':', $mib);
                    }
                }
            } elseif (str_starts_with($flag, '-m') && strlen($flag) > 2) {
                $mib = substr($flag, 2);
                if (str_starts_with($mib, '+')) {
                    $this->mibs[] = substr($mib, 1);
                    $this->mibs = array_values(array_unique($this->mibs));
                } else {
                    $this->mibs = explode(':', $mib);
                }
            } elseif ($flag === '-M' && isset($wrapped[$i + 1])) {
                $mibDir = $wrapped[++$i];
                if (is_string($mibDir)) {
                    $this->mibDirs = explode(':', $mibDir);
                }
            } elseif (str_starts_with($flag, '-M') && strlen($flag) > 2) {
                $this->mibDirs = explode(':', substr($flag, 2));
            }
        }

        if ($hasCustomOutputFlags && ! in_array('-Pu', $wrapped, true)) {
            $this->allowUnderlines = true;
        }

        return $this;
    }
}
