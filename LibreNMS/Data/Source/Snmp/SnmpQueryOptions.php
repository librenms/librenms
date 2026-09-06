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
            return $this->applyQuickPrintDefaults();
        }

        $this->applySnmpLibraryDefaults();

        $flagsArray = Arr::wrap($flags);

        for ($i = 0; $i < count($flagsArray); $i++) {
            $flag = $flagsArray[$i];
            if (! is_string($flag)) {
                continue;
            }

            if (str_starts_with($flag, '-O')) {
                foreach (str_split(substr($flag, 2)) as $outopt) {
                    match ($outopt) {
                        'a' => $this->stringFormat = SnmpStringOutput::Ascii,
                        'x' => $this->stringFormat = SnmpStringOutput::Hex,
                        'f' => $this->oidFormat = SnmpOidOutput::Full,
                        's' => $this->oidFormat = SnmpOidOutput::Suffix,
                        'S' => $this->oidFormat = SnmpOidOutput::Module,
                        'u' => $this->oidFormat = SnmpOidOutput::Ucd,
                        'n' => $this->oidFormat = SnmpOidOutput::Numeric,
                        'b' => $this->numericIndexes = true,
                        'e' => $this->numericEnums = true,
                        'E' => $this->escapeQuotes = true,
                        'Q' => $this->quickPrint = true,
                        't' => $this->numericTimeticks = true,
                        'T' => $this->printHexText = true,
                        'U' => $this->printUnits = false,
                        'X' => $this->extendedIndex = true,
                        default => throw new \Exception("Unknown option -O$outopt"),
                    };
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
                $this->allowUnderscores = str_contains(substr($flag, 2), 'u');
            } elseif (str_starts_with($flag, '-I')) {
                if (str_contains(substr($flag, 2), 'h')) {
                    $this->applyDisplayHints = false;
                }
            } elseif ($flag === '-m' || (str_starts_with($flag, '-m') && strlen($flag) > 2)) {
                $this->addMibs($flag === '-m' ? $this->nextArg($flagsArray, $i) : substr($flag, 2));
            } elseif ($flag === '-M' || (str_starts_with($flag, '-M') && strlen($flag) > 2)) {
                $mibDir = $flag === '-M' ? $this->nextArg($flagsArray, $i) : substr($flag, 2);
                if ($mibDir !== null) {
                    $this->mibDirs = explode(':', $mibDir);
                }
            }
        }

        return $this;
    }

    private function applyQuickPrintDefaults(): self
    {
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

    private function applySnmpLibraryDefaults(): void
    {
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
    }

    private function addMibs(?string $mib): void
    {
        if ($mib === null) {
            return;
        }

        if (str_starts_with($mib, '+')) {
            $this->mibs[] = substr($mib, 1);
            $this->mibs = array_values(array_unique($this->mibs));
        } else {
            $this->mibs = explode(':', $mib);
        }
    }

    private function nextArg(array $wrapped, int &$i): ?string
    {
        return isset($wrapped[$i + 1]) && is_string($wrapped[$i + 1]) ? $wrapped[++$i] : null;
    }
}
