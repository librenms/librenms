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
use LibreNMS\Enum\SnmpOidOutput;
use LibreNMS\Enum\SnmpStringOutput;

class SnmpQueryOptions
{
    /**
     * @param  string[]  $mibs
     * @param  string[]  $mibDirs
     */
    public function __construct(
        public string $context = '',
        public array $mibs = ['SNMPv2-TC', 'SNMPv2-MIB', 'IF-MIB', 'IP-MIB', 'TCP-MIB', 'UDP-MIB', 'NET-SNMP-VACM-MIB'],
        public array $mibDirs = [],

        // Query behavior
        public bool $allowBulk = true,
        public bool $tolerateUnorderedIndexes = false,
        public bool $includeGivenOid = false,

        // OID formatting
        public bool $numericIndexes = false,
        public bool $outputMibNames = true,
        public SnmpOidOutput $oidFormat = SnmpOidOutput::Module,

        // Value formatting
        public bool $escapeQuotes = false,
        public bool $numericEnums = true,
        public bool $numericTimeticks = true,
        public bool $printHexText = false,
        public bool $printUnits = false,
        public bool $applyDisplayHints = true,
        public SnmpStringOutput $stringFormat = SnmpStringOutput::Guess,

        // Output presentation
        public bool $quickPrint = true,
        public bool $extendedIndex = true,
        public bool $allowUnderscores = false,
    ) {
    }

    /**
     * @param  string[]|string|null  $flags
     */
    public function parseCli(array|string|null $flags): self
    {
        if ($flags === null) {
            $this->context = '';
            $this->allowBulk = true;
            $this->tolerateUnorderedIndexes = false;
            $this->escapeQuotes = false;
            $this->numericIndexes = false;
            $this->outputMibNames = true;
            $this->numericEnums = true;
            $this->numericTimeticks = true;
            $this->printHexText = false;
            $this->printUnits = false;
            $this->applyDisplayHints = true;
            $this->quickPrint = true;
            $this->extendedIndex = true;
            $this->allowUnderscores = false;
            $this->stringFormat = SnmpStringOutput::Guess;
            $this->oidFormat = SnmpOidOutput::Module;

            return $this;
        }

        // Reset to SNMP library defaults
        $this->context = '';
        $this->allowBulk = true;
        $this->tolerateUnorderedIndexes = false;
        $this->numericIndexes = false;
        $this->outputMibNames = false;
        $this->numericEnums = false;
        $this->numericTimeticks = false;
        $this->printHexText = false;
        $this->printUnits = true;
        $this->applyDisplayHints = false;
        $this->quickPrint = false;
        $this->extendedIndex = false;
        $this->allowUnderscores = false;
        $this->stringFormat = SnmpStringOutput::Guess;
        $this->oidFormat = SnmpOidOutput::Module;

        $wrapped = Arr::wrap($flags);
        $hasCustomOutputFlags = false;

        for ($i = 0; $i < count($wrapped); $i++) {
            $flag = $wrapped[$i];
            if (! is_string($flag)) {
                continue;
            }

            if (str_starts_with($flag, '-O')) {
                $hasCustomOutputFlags = true;
                foreach (str_split(substr((string) $flag, 2)) as $outopt) {
                    switch ($outopt) {
                        case 'a':
                            $this->stringFormat = SnmpStringOutput::Ascii;
                            break;
                        case 'x':
                            $this->stringFormat = SnmpStringOutput::Hex;
                            break;
                        case 'f':
                            $this->oidFormat = SnmpOidOutput::Full;
                            break;
                        case 's':
                            $this->oidFormat = SnmpOidOutput::Suffix;
                            break;
                        case 'S':
                            $this->oidFormat = SnmpOidOutput::Module;
                            break;
                        case 'u':
                            $this->oidFormat = SnmpOidOutput::Ucd;
                            break;
                        case 'n':
                            $this->oidFormat = SnmpOidOutput::Numeric;
                            break;
                        case 'b':
                            $this->numericIndexes = true;
                            break;
                        case 'e':
                            $this->numericEnums = true;
                            break;
                        case 'E':
                            $this->escapeQuotes = true;
                            break;
                        case 'Q':
                            $this->quickPrint = true;
                            break;
                        case 't':
                            $this->numericTimeticks = true;
                            break;
                        case 'T':
                            $this->printHexText = true;
                            break;
                        case 'U':
                            $this->printUnits = false;
                            break;
                        case 'X':
                            $this->extendedIndex = true;
                            break;
                        default:
                            throw new \Exception("Unknown option -O$outopt");
                    }
                }
            } elseif (str_starts_with($flag, '-C')) {
                $opts = substr($flag, 2);
                if (str_contains($opts, 'c')) {
                    $this->tolerateUnorderedIndexes = true;
                }
                if (str_contains($opts, 'i')) {
                    $this->includeGivenOid = true;
                }
            } elseif (str_starts_with($flag, '-P')) {
                $opts = substr($flag, 2);
                $this->allowUnderscores = str_contains($opts, 'u');
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

        return $this;
    }
}
